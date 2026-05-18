<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Databot;

/**
 * @method Databot|null find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Databot|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method Databot[]    findAll()
 * @method Databot[]    findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 *
 * @extends AbstractRepository<Databot>
 */
final class DatabotRepository extends AbstractRepository
{
    public const string IMAGE_QUALITY = 'no-ref-image-metrics';
    public const string CETAF = 'cetaf_metadata';
    public const string HESPI_SHEET = 'hespi_v1_sheet_detector';

    public function getByName(string $name): ?Databot
    {
        return $this->findOneBy(['name' => $name]);
    }
}
