<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Specimen\Specimen;
use Doctrine\ORM\QueryBuilder;
use Nette\Security\User;

/**
 * @method Photos|null find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Photos|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Photos[]    findAll()
 * @method Photos[]    findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 *
 * @extends AbstractRepository<Photos>
 */
class PhotosRepository extends AbstractRepository
{
    /**
     * if curator deletes a file in his bucket and the image i) is processed or ii) has Import error, then we have an "orphaned" row.
     *
     * @return Photos[]
     */
    public function getOrphanable(User $user): array
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.status IN (:status)')->setParameter('status', [PhotosStatus::WAITING, PhotosStatus::IMAGE_CONTROL_ERROR])->orderBy('p.lastEdit', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Photos[]
     */
    public function findLastImported(User $user): array
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.status IN (:status)')->setParameter('status', PhotosStatus::PASSED)->orderBy('p.lastEdit', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getDefaultDatasource(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('p')->andWhere('p.herbarium = :userHerbarium  OR :isAdmin = true')->setParameter('userHerbarium', $user->getIdentity()->getCurrentHerbariumId())->setParameter('isAdmin', $user->isInRole('ROLE_ADMIN'));
    }

    public function getAllPublishedPhotosDatasource(): QueryBuilder
    {
        return $this->createQueryBuilder('p')->andWhere('p.status = :publicStatus')->setParameter('publicStatus', PhotosStatus::PUBLISHED);
    }

    public function getPublishablePhotosDatasource(User $user): QueryBuilder
    {
        return $this->getDefaultDatasource($user)->andWhere('p.status = :status')->setParameter('status', PhotosStatus::SPECIMEN_CONTROL_OK);
    }

    /**
     * @return Photos[]
     */
    public function getPublicPhotosOfSpecimen(Specimen $specimen): array
    {
        return $this->findBy(['specimenId' => $specimen->id, 'herbarium' => $specimen->herbarium, 'status' => PhotosStatus::PUBLISHED]);
    }

    public function getPublicPhoto(int $id): ?Photos
    {
        return $this->findOneBy(['id' => $id, 'status' => PhotosStatus::PUBLISHED]);
    }

    public function getPublicPhotoBySpecimenSid(string $sid): ?Photos
    {
        return $this->findOneBy(['specimenPid' => $sid, 'status' => PhotosStatus::PUBLISHED]);
    }

    public function getPhoto(User $user, int $id): ?Photos
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.id = :id')->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getPhotoWithError(User $user, int $id): ?Photos
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.id = :id')->andWhere('p.status = :status')->setParameter('id', $id)->setParameter('status', $this->getControlErrorStatus());

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * @return Photos[]
     */
    public function getPhotosWithError(User $user): array
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.status = :status')->setParameter('status', $this->getControlErrorStatus());

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Photos[]
     */
    public function getAllPhotosOfSpecimen(User $user, Specimen $specimen): array
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.specimenId = :specimenId')->setParameter('specimenId', $specimen->id);

        return $qb->getQuery()->getResult();
    }

    public function getMaxPhotoStatusOfSpecimen(User $user, Specimen $specimen): PhotosStatus
    {
        $qb = $this->getDefaultDatasource($user)
            ->select('p, status')
            ->andWhere('p.specimenId = :specimenId')
            ->setParameter('specimenId', $specimen->id)
            ->join('p.status', 'status')
            ->orderBy('status.succession', 'DESC')
            ->setMaxResults(1);

        $photo = $qb->getQuery()->getOneOrNullResult();

        return $photo?->status;
    }

    /**
     * @return Photos[]
     */
    public function findUnprocessedPhotos(User $user): array
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.status IN (:status)')->setParameter('status', [PhotosStatus::WAITING, PhotosStatus::IMAGE_CONTROL_ERROR]);

        return $qb->getQuery()->getResult();
    }

    protected function getControlErrorStatus(): PhotosStatus
    {
        return $this->getEntityManager()->getReference(PhotosStatus::class, PhotosStatus::IMAGE_CONTROL_ERROR);
    }

    public function countOfPublic(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.status = :status')
            ->setParameter('status', PhotosStatus::PUBLISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
