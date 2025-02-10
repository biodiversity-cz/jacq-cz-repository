<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Specimen;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;

class PhotoService extends BaseEntityService
{

    protected string $entityName = Photos::class;

    public function specimenHasPublicPhotos(Specimen $specimen): bool
    {
        return count($this->getPublicPhotosOfSpecimen($specimen)) > 0;
    }

    public function getDefaultDatasource(): QueryBuilder
    {
        return $this->repository->createQueryBuilder('a')
            ->andWhere('a.herbarium = :herbarium')
            ->setParameter('herbarium', $this->user->getIdentity()->herbarium);
    }

    /**
     * @return Photos[]
     */
    public function getPublicPhotosOfSpecimen(Specimen $specimen): array
    {
        return $this->repository->findBy(['specimenId' => $specimen->getSpecimenId(), 'herbarium' => $specimen->getHerbarium(), 'status' => PhotosStatus::PASSED_PUBLIC]);
    }

    public function getPhotoReference(int $id): Photos
    {
        return $this->entityManager->getReference($this->entityName, $id);
    }

    public function getPublicPhoto(int $id): ?Photos
    {
        return $this->repository->findOneBy(['id' => $id, 'status' => PhotosStatus::PASSED_PUBLIC]);
    }

    public function getPublicStatus(): PhotosStatus
    {
        return $this->entityManager->getReference(PhotosStatus::class, PhotosStatus::PUBLIC);
    }

    public function getControlErrorStatus(): PhotosStatus
    {
        return $this->entityManager->getReference(PhotosStatus::class, PhotosStatus::CONTROL_ERROR);
    }

    public function getWaitingStatus(): PhotosStatus
    {
        return $this->entityManager->getReference(PhotosStatus::class, PhotosStatus::WAITING);
    }

    public function getPhotoWithError(int $id): ?Photos
    {
        return $this->repository->findOneBy(['id' => $id, 'herbarium' => $this->user->getIdentity()->herbarium, 'status' => $this->getControlErrorStatus()]);
    }

    /**
     * @return Photos[]
     */
    public function getPhotosWithError(): array
    {
        return $this->repository->findBy(['herbarium' => $this->user->getIdentity()->herbarium, 'status' => $this->getControlErrorStatus()]);
    }

    public function findUnprocessedPhotoByOriginalFilename(string $filename): ?Photos
    {
        return $this->repository->findOneBy(['status' => [PhotosStatus::WAITING, PhotosStatus::CONTROL_ERROR], 'herbarium' => $this->user->getIdentity()->herbarium, 'originalFilename' => $filename]);
    }

    public function findUnprocessedPhotos(bool $useBarcode): array
    {
        $photos =  $this->repository->findBy(['status' => [PhotosStatus::WAITING, PhotosStatus::CONTROL_ERROR], 'herbarium' => $this->user->getIdentity()->herbarium, 'useBarcode' => $useBarcode]);
        $unprocessedPhotos = [];
        foreach ($photos as $photo) {
            $unprocessedPhotos[$photo->getOriginalFilename()] = $photo;
        }
        return $unprocessedPhotos;
    }

    /**
     * @return Photos[]
     */
    public function findLastImported(): array
    {
        return $this->repository->findBy(['herbarium' => $this->user->getIdentity()->herbarium, 'status' => [PhotosStatus::CONTROL_OK, PhotosStatus::PUBLIC, PhotosStatus::HIDDEN]], ['lastEdit' => Criteria::DESC], 30);
    }

    /**
     * @return Photos[]
     */
    public function findPotentialDuplicates(Photos $photo): array
    {
        return $this->repository->findBy(['herbarium' => $photo->getHerbarium(), 'specimenId' => $photo->getSpecimenId(), 'archiveFileSize' => $photo->getArchiveFileSize(), 'status' => [PhotosStatus::CONTROL_OK, PhotosStatus::PUBLIC, PhotosStatus::HIDDEN]]);
    }

}
