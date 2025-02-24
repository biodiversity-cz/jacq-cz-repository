<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Specimen\Specimen;
use Doctrine\ORM\QueryBuilder;
use Nette\Security\User;

/**
 * @method Photos|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Photos|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Photos[] findAll()
 * @method Photos[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Photos>
 */
final class PhotosRepository extends AbstractRepository
{

    public function findOneByArchiveFilename(string $archiveFilename): ?Photos
    {
        return $this->findOneBy(['archiveFilename' => $archiveFilename]);
    }

    /**
     * if curator deletes a file in his bucket and the image i) is processed or ii) has Import error, then we have an "orphaned" row.
     *
     * @return Photos[]
     */
    public function getOrphananble(Herbaria $herbarium): array
    {
        return $this->findBy(['status' => [PhotosStatus::WAITING, PhotosStatus::CONTROL_ERROR], 'herbarium' => $herbarium]);
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
        return $this->createQueryBuilder('p')->andWhere('p.herbarium = :userHerbarium  OR :isAdmin = true')->setParameter('userHerbarium', $user->getIdentity()->herbarium)->setParameter('isAdmin', $user->isInRole('ROLE_ADMIN'));
    }

    /**
     * @return Photos[]
     */
    public function getPublicPhotosOfSpecimen(Specimen $specimen): array
    {
        return $this->findBy(['specimenId' => $specimen->getNumericPartOfId(), 'herbarium' => $specimen->getHerbarium(), 'status' => PhotosStatus::PASSED_PUBLIC]);
    }

    public function getPublicPhoto(int $id): ?Photos
    {
        return $this->findOneBy(['id' => $id, 'status' => PhotosStatus::PASSED_PUBLIC]);
    }

    public function getPhoto(User $user, int $id): ?Photos
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.id = :id')->setParameter('id', $id);

        return $qb->getQuery()->getSingleResult();
    }

    public function getPhotoWithError(User $user, int $id): ?Photos
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.id = :id')->andWhere('p.status = :status')->setParameter('id', $id)->setParameter('status', $this->getControlErrorStatus());

        return $qb->getQuery()->getResult();
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
        $qb = $this->getDefaultDatasource($user)->andWhere('p.specimenId = :specimenId')->setParameter('specimenId', $specimen->getNumericPartOfId());

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Photos[]
     */
    public function findUnprocessedPhotos(User $user): array
    {
        $qb = $this->getDefaultDatasource($user)->andWhere('p.status IN (:status)')->setParameter('status', [PhotosStatus::WAITING, PhotosStatus::CONTROL_ERROR]);

        return $qb->getQuery()->getResult();
    }

    protected function getControlErrorStatus(): PhotosStatus
    {
        return $this->getEntityManager()->getReference(PhotosStatus::class, PhotosStatus::CONTROL_ERROR);
    }

}
