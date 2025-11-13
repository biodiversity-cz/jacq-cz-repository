<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\PhotosStatus;

/**
 * @method PhotosStatus|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method PhotosStatus|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method PhotosStatus[] findAll()
 * @method PhotosStatus[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<PhotosStatus>
 */
final class PhotosStatusRepository extends AbstractRepository
{

}
