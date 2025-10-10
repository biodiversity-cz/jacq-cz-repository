<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\ImageDownloadLog;
use Doctrine\ORM\EntityManagerInterface;

class ImageDownloadLogService extends BaseEntityService
{

    protected string $entityName = ImageDownloadLog::class;

    public function logDownload(
        int $photoId,
        string $imageType,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $referrer = null
    ): void {
        $downloadRequest = new ImageDownloadLog();
        $downloadRequest->setPhotoId($photoId)
            ->setImageType($imageType)
            ->setIpAddress($ipAddress)
            ->setUserAgent($userAgent)
            ->setReferrer($referrer)
            ->setCreatedAt();

        $this->entityManager->persist($downloadRequest);
        $this->entityManager->flush();
    }
}
