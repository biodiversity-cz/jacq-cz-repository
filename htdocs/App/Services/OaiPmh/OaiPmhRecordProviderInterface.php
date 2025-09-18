<?php declare(strict_types=1);

namespace App\Services\OaiPmh;

use App\Model\Database\Entity\Photos;

/**
 * Interface for providing records to OAI-PMH responses
 */
interface OaiPmhRecordProviderInterface
{
    /**
     * Get total count of public records
     */
    public function getTotalRecordsCount(): int;

    /**
     * Get records with pagination and optional filtering
     * Returns an iterator for memory efficiency
     *
     * @param \DateTimeInterface|null $from Optional from date filter
     * @param \DateTimeInterface|null $until Optional until date filter
     * @param string|null $set Optional set filter
     * @param int $offset Pagination offset
     * @param int $limit Pagination limit
     * @return \Iterator<Photos>
     */
    public function getRecords(
        ?\DateTimeInterface $from = null,
        ?\DateTimeInterface $until = null,
        ?string $set = null,
        int $offset = 0,
        int $limit = 100
    ): \Iterator;

    /**
     * Get a single record by identifier
     */
    public function getRecord(string $identifier): ?Photos;

    /**
     * Get available sets (herbaria in our case)
     * @return array<string, string> Array of set spec => set name
     */
    public function getAvailableSets(): array;

    /**
     * Get the earliest datestamp in the repository
     */
    public function getEarliestDatestamp(): ?\DateTimeInterface;

    /**
     * Check if a record exists by identifier
     */
    public function recordExists(string $identifier): bool;
}