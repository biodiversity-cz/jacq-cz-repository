<?php

declare(strict_types=1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\DatabotResult;
use App\Model\Database\Entity\Photos;

/**
 * @method DatabotResult|null find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method DatabotResult|null findOneBy(array $criteria, array $orderBy = NULL)
 * @method DatabotResult[]    findAll()
 * @method DatabotResult[]    findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 *
 * @extends AbstractRepository<DatabotResult>
 */
final class DatabotResultRepository extends AbstractRepository
{
    public function getPercentilOfMetric(int $databotId, string $metricName, Photos $photo, bool $compareGlobal = true): int
    {
        $sql = "WITH target AS (
                SELECT (elem->>'value')::float AS val
                FROM databots.databot_results dr
                 , LATERAL jsonb_array_elements(dr.result_data) AS elem
                WHERE dr.databot_id = :databot_id AND dr.photo_id = :photoId AND elem->>'name' = :metric
            )
            SELECT COUNT(*) FILTER (WHERE (elem->>'value')::float <= target.val)::float / COUNT(*) AS percentile
            FROM databots.databot_results dr,
                 LATERAL jsonb_array_elements(dr.result_data) AS elem,
                 target
            WHERE databot_id = :databot_id AND elem->>'name' = :metric";

        return (int) round(100 * $this->getEntityManager()->getConnection()->executeQuery($sql, ['databot_id' => $databotId, 'metric' => $metricName, 'photoId' => $photo->id])->fetchOne());
    }

    public function findLatestByPhotoAndDatabotName(Photos $photo, string $databotName): ?DatabotResult
    {
        return $this->createQueryBuilder('r')
            ->join('r.databot', 'd')
            ->where('r.photo = :photo')
            ->andWhere('d.name = :name')
            ->setParameter('photo', $photo)
            ->setParameter('name', $databotName)
            ->orderBy('d.version', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    // vypsat si fotky s určenými hodnotami
    //
    // SELECT photo_id, (elem->>'value')::float AS val
    // FROM databots.databot_results dr
    // , LATERAL jsonb_array_elements(dr.result_data) AS elem
    // WHERE dr.databot_id = 2
    // AND elem->>'name' = 'brisque_score'
    // AND (elem->>'value')::float > 60;
}
