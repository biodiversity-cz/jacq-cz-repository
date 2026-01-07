<?php declare(strict_types=1);

namespace App\Services\OaiPmh;

use App\Model\Database\Entity\Photos;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Service providing records for OAI-PMH responses with memory efficiency
 */
final class OaiPmhRecordProvider implements OaiPmhRecordProviderInterface
{
    public function __construct(
        private readonly PhotoService $photoService,
        private readonly HerbariumService $herbariumService
    ) {
    }

    public function getTotalRecordsCount(): int
    {
        $qb = $this->photoService->getAllPublishedPhotosDatasource()
            ->select('COUNT(p.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getRecords(
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $until = null,
        ?string $set = null,
        int $offset = 0,
        int $limit = 100
    ): \Iterator {
        $qb = $this->photoService->getAllPublishedPhotosDatasource();

        // Add joins for related data needed for metadata
        $qb->leftJoin('p.herbarium', 'h')
           ->leftJoin('h.license', 'l')
           ->addSelect('h', 'l');

        // Apply date filters on lastEdit field
        if ($from !== null) {
            $qb->andWhere('p.lastEdit >= :from')
               ->setParameter('from', $from);
        }

        if ($until !== null) {
            $qb->andWhere('p.lastEdit <= :until')
               ->setParameter('until', $until);
        }

        // Apply set filter (herbarium)
        if ($set !== null) {
            $qb->andWhere('h.acronym = :set')
               ->setParameter('set', $set);
        }

        // Order by lastEdit for consistent pagination
        $qb->orderBy('p.lastEdit', 'ASC')
           ->addOrderBy('p.id', 'ASC');

        // Apply pagination
        $qb->setFirstResult($offset)
           ->setMaxResults($limit);

        // Use Doctrine Paginator for memory efficiency
        $paginator = new Paginator($qb, false);

        return $this->createMemoryEfficientIterator($paginator);
    }

    public function getRecord(string $identifier): ?Photos
    {
        // Extract photo ID from OAI identifier
        $photoId = $this->extractPhotoIdFromIdentifier($identifier);
        if ($photoId === null) {
            return null;
        }

        $qb = $this->photoService->getAllPublishedPhotosDatasource();
        $qb->leftJoin('p.herbarium', 'h')
           ->leftJoin('h.license', 'l')
           ->addSelect('h', 'l')
           ->andWhere('p.id = :id')
           ->setParameter('id', $photoId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getAvailableSets(): array
    {
        $herbaria = $this->herbariumService->findAll();
        $sets = [];

        foreach ($herbaria as $herbarium) {
            $sets[$herbarium->acronym] = $herbarium->fullname ?? $herbarium->acronym;
        }

        return $sets;
    }

    public function getEarliestDatestamp(): ?\DateTimeInterface
    {
        $qb = $this->photoService->getAllPublishedPhotosDatasource();
        $qb->select('MIN(p.lastEdit)');

        $result = $qb->getQuery()->getSingleScalarResult();

        return $result ? new \DateTimeImmutable($result) : null;
    }

    public function recordExists(string $identifier): bool
    {
        $photoId = $this->extractPhotoIdFromIdentifier($identifier);
        if ($photoId === null) {
            return false;
        }

        $qb = $this->photoService->getAllPublishedPhotosDatasource();
        $qb->select('COUNT(p.id)')
           ->andWhere('p.id = :id')
           ->setParameter('id', $photoId);

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Create a memory-efficient iterator that processes records in batches
     */
    private function createMemoryEfficientIterator(Paginator $paginator): \Iterator
    {
        // Process results in smaller batches to avoid memory issues
        $batchSize = 50;
        $offset = 0;

        while (true) {
            $query = $paginator->getQuery();
            $query->setFirstResult($offset);
            $query->setMaxResults($batchSize);


            $iterator = $query->toIterable();
            $hasResults = false;

            foreach ($iterator as $row) {
                $hasResults = true;
                yield $row; // Doctrine iterate returns array, we want the entity

                // Clear entity manager to free memory
                if (($offset % 100) === 0) {
                    $this->photoService->clearEntityManager();
                }
            }

            if (!$hasResults) {
                break;
            }

            $offset += $batchSize;
        }
    }

    /**
     * Extract photo ID from OAI identifier
     * Expected format: oai:domain.com:photo-{id}
     */
    private function extractPhotoIdFromIdentifier(string $identifier): ?int
    {
        if (!str_starts_with($identifier, 'oai:')) {
            return null;
        }

        $parts = explode(':', $identifier);
        if (count($parts) < 3) {
            return null;
        }

        $localId = end($parts);
        if (!str_starts_with($localId, 'photo-')) {
            return null;
        }

        $id = substr($localId, 6); // Remove 'photo-' prefix

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * Generate OAI identifier for a photo
     */
    public function generateIdentifier(Photos $photo, string $domain): string
    {
        return sprintf('oai:%s:photo-%d', $domain, $photo->id);
    }
}
