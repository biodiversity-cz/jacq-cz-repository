<?php

declare(strict_types=1);

namespace App\Services;

use Doctrine\ORM\EntityManagerInterface;

class StatisticsService
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    public function overviewOfProcessing()
    {
        $sql = '
        select count(*), s.name as status, h.acronym as herbarium
            from photos p
            JOIN photos_status s ON (p.status_id = s.id)
            JOIN herbaria h ON (h.id = p.herbarium_id)
            group by s.name, h.acronym
            order by s.name, h.acronym';

        $result = $this->entityManager->getConnection()->executeQuery($sql);

        return $result->fetchAllAssociative();
    }
}
