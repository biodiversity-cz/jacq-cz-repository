<?php declare(strict_types=1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Specimen\Specimen;
use Doctrine\ORM\QueryBuilder;
use Nette\Security\User;

class PhotoService extends BaseEntityService
{

    protected string $entityName = Photos::class;

    public function specimenHasPublicPhotos(Specimen $specimen): bool
    {
        return count($this->getPublicPhotosOfSpecimen($specimen)) > 0;
    }

    /**
     * @return Photos[]
     */
    public function getPublicPhotosOfSpecimen(Specimen $specimen): array
    {
        return $this->repository->getPublicPhotosOfSpecimen($specimen);
    }

    public function getDefaultDatasource(User $user): QueryBuilder
    {
        return $this->repository->getDefaultDatasource($user);
    }

    /**
     * @return Photos[]
     */
    public function getAllPhotosOfSpecimen(User $user, Specimen $specimen): array
    {
        return $this->repository->getAllPhotosOfSpecimen($user, $specimen);
    }

    public function getPhotoReference(int $id): Photos
    {
        return $this->entityManager->getReference($this->entityName, $id);
    }

    public function getPhoto(User $user, int $id): ?Photos
    {
        if ($user->isLoggedIn()) {
            return $this->repository->getPhoto($user, $id);
        } else {
            return $this->repository->getPublicPhoto($id);
        }
    }

    public function getPublicPhoto(int $id): ?Photos
    {
        return $this->repository->getPublicPhoto($id);
    }

    public function getWaitingStatus(): PhotosStatus
    {
        return $this->entityManager->getReference(PhotosStatus::class, PhotosStatus::WAITING);
    }

    public function getPhotoWithError(User $user, int $id): ?Photos
    {
        return $this->repository->getPhotoWithError($user, $id);
    }

    /**
     * @return Photos[]
     */
    public function getPhotosWithError(User $user): array
    {
        return $this->repository->getPhotosWithError($user);
    }

    /**
     * @return Photos[]
     */
    public function findUnprocessedPhotos(User $user): array
    {
        $unprocessedPhotos = [];
        foreach ($this->repository->findUnprocessedPhotos($user) as $photo) {
            $unprocessedPhotos[$photo->getOriginalFilename()] = $photo;
        }

        return $unprocessedPhotos;
    }

    /**
     * @return Photos[]
     */
    public function findPotentialDuplicates(Photos $photo): array
    {
        return $this->repository->findBy(['herbarium' => $photo->getHerbarium(), 'specimenId' => $photo->getSpecimenId(), 'archiveFileSize' => $photo->getArchiveFileSize(), 'status' => PhotosStatus::PASSED]);
    }

    /**
     * how many records area waiting for processing by the import pipeline
     *
     * @return mixed[]
     */
    public function pendingPhotosCount(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('h.id, h.acronym, COUNT(p.id) AS count')
            ->from('App\Model\Database\Entity\Photos', 'p')
            ->andWhere('p.status = :status')
            ->join('p.herbarium', 'h')
            ->groupBy('h.id')
            ->setParameter('status', PhotosStatus::WAITING);

        return $qb->getQuery()->getResult();
    }

}
