<?php

declare(strict_types=1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\ExcludedDownloadLog;
use Doctrine\ORM\EntityManagerInterface;

class ExcludedDownloadLogService extends BaseEntityService
{
    protected string $entityName = ExcludedDownloadLog::class;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    /**
     * Check if an IP address is excluded from logging
     * Supports wildcard matching for partial IPs.
     */
    public function isIpExcluded(string $ip): bool
    {
        // Get all excluded IPs
        $excludedIps = $this->getAllExcludedIps();

        foreach ($excludedIps as $excludedIp) {
            $excludedIpValue = $excludedIp->ip;

            // Exact match
            if ($ip === $excludedIpValue) {
                return true;
            }

            // Wildcard match - check if IP starts with the excluded pattern
            if (str_starts_with($ip, $excludedIpValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all excluded IP addresses.
     *
     * @return ExcludedDownloadLog[]
     */
    public function getAllExcludedIps(): array
    {
        return $this->findAll();
    }

    /**
     * Add a new IP to the exclusion list.
     */
    public function addExcludedIp(string $ip, string $description): ExcludedDownloadLog
    {
        $excludedIp = new ExcludedDownloadLog();
        $excludedIp->setIp($ip)
            ->setDescription($description);

        $this->entityManager->persist($excludedIp);
        $this->entityManager->flush();

        return $excludedIp;
    }

    /**
     * Remove an IP from the exclusion list.
     */
    public function removeExcludedIp(string $ip): void
    {
        $excludedIp = $this->findOneBy(['ip' => $ip]);
        if (null !== $excludedIp) {
            $this->entityManager->remove($excludedIp);
            $this->entityManager->flush();
        }
    }
}
