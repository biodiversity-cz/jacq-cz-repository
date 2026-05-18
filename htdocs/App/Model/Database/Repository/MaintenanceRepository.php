<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Maintenance;

/**
 * @method Maintenance|null find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Maintenance|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Maintenance[]    findAll()
 * @method Maintenance[]    findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 *
 * @extends AbstractRepository<Maintenance>
 */
final class MaintenanceRepository extends AbstractRepository
{
    public function getValid(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.expiresAt IS NULL OR m.expiresAt > :now')
            ->orderBy('m.expiresAt', 'ASC')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()->getResult();
    }
}
