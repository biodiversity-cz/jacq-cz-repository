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

    public function getAllPublishedPhotosDatasource(): QueryBuilder
    {
        return $this->repository->getAllPublishedPhotosDatasource();
    }

    public function getPublishablePhotosDatasource(User $user): QueryBuilder
    {
        return $this->repository->getPublishablePhotosDatasource($user);
    }

    /**
     * Mark all publishable photos as WAITING_FOR_PUBLISHING in a single bulk operation
     * Reuses the same criteria as getPublishablePhotosDatasource() for consistency
     * Executes as a single DQL UPDATE query - no entity loading overhead
     */
    public function markAllPublishableAsWaitingForPublishing(User $user): int
    {
        $qb = $this->getPublishablePhotosDatasource($user);
        $qb->update(Photos::class, 'p')
            ->set('p.status', ':newStatus')
            ->set('p.lastEdit', 'CURRENT_TIMESTAMP()')
            ->setParameter('newStatus', PhotosStatus::WAITING_FOR_PUBLISHING);

        return $qb->getQuery()->execute();
    }

    /**
     * @return Photos[]
     */
    public function getAllPhotosOfSpecimen(User $user, Specimen $specimen): array
    {
        return $this->repository->getAllPhotosOfSpecimen($user, $specimen);
    }

    public function getMaxPhotoStatusOfSpecimen(User $user, Specimen $specimen): PhotosStatus
    {
        return $this->repository->getMaxPhotoStatusOfSpecimen($user, $specimen);
    }

    public function getPhotoReference(int $id): Photos
    {
        return $this->entityManager->getReference($this->entityName, $id);
    }

    public function getPhoto(User $user, int $id): ?Photos
    {
        if ($user->isLoggedIn()) {
            $photoIncludingPrivate =  $this->repository->getPhoto($user, $id);
            if ($photoIncludingPrivate!==null) {
                return $photoIncludingPrivate;
            }
        }
        return $this->repository->getPublicPhoto($id);
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
            $unprocessedPhotos[$photo->originalFilename] = $photo;
        }

        return $unprocessedPhotos;
    }

    /**
     * @return Photos[]
     */
    public function findPotentialDuplicates(Photos $photo): array
    {
        return $this->repository->findBy(['herbarium' => $photo->herbarium, 'specimenId' => $photo->specimenId, 'archiveFileSize' => $photo->archiveFileSize, 'status' => PhotosStatus::PASSED]);
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

    public function clearEntityManager(): void
    {
        $this->entityManager->clear();
    }

    public function findOneByArk(string $ark): ?Photos
    {
        $qb = $this->repository->createQueryBuilder('p');
        $qb ->andWhere('p.ark LIKE :ark')
            ->setParameter('ark', $ark.'%');

        return $qb->getQuery()->getResult();
    }

}
