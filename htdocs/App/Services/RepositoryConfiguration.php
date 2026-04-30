<?php declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ConfigurationException;
use App\Model\Database\Entity\Photos;

final readonly class RepositoryConfiguration
{

    /**
     * @param mixed[] $config
     */
    public function __construct(protected array $config, protected TempDir $tempDir)
    {
    }

    public function getRecentlyUsedBucketSuffix(): string
    {
        return $this->getKey('recentBucketSuffix');
    }

    public function getArchiveBucket(Photos $photo): string
    {
        return $this->getRepositoryArchiveBucketPrefix() . $photo->bucketSuffix;
    }

    public function getRepositoryArchiveBucketPrefix(): string
    {
        return $this->getKey('archiveBucketPrefix', 'Archive bucket prefix not set.');
    }

    public function getArkProperties(): array
    {
        return $this->getKey('ark', 'Ark properties not set.');
    }

    public function getRecentlyUsedArchiveBucket(): string
    {
        return $this->getRepositoryArchiveBucketPrefix() . $this->getRecentlyUsedBucketSuffix();
    }


    protected function getKey(string $key, string $msg = ''): mixed
    {
        if (!isset($this->config[$key])) {
            $text = $msg === '' ? 'Configuration parameter ' . strtoupper($key) . ' not set!' : $msg;

            throw new ConfigurationException($text);
        }

        return $this->config[$key];
    }

    public function getImageServerBucket(Photos $photo): string
    {
        return $this->getRepositoryImageServerBucketPrefix() . $photo->bucketSuffix;
    }

    public function getRepositoryImageServerBucketPrefix(): string
    {
        return $this->getKey('jp2BucketPrefix', 'Image server bucket prefix not set.');
    }

    public function getRecentlyUsedImageServerBucket(): string
    {
        return $this->getRepositoryImageServerBucketPrefix() . $this->getRecentlyUsedBucketSuffix();
    }

    public function getDatabotThumbsBucket(Photos $photo): string
    {
        return $this->getRepositoryDatabotThumbsBucketPrefix() . $photo->bucketSuffix;
    }

    public function getRepositoryDatabotThumbsBucketPrefix(): string
    {
        return $this->getKey('thumbBucketPrefix', 'Thumbs bucket prefix not set.');
    }

    public function getRecentlyUsedDatabotThumbsBucket(): string
    {
        return $this->getRepositoryDatabotThumbsBucketPrefix() . $this->getRecentlyUsedBucketSuffix();
    }


    public function getJp2Quality(): int
    {
        return $this->getKey('jp2Quality', 'Compression for image server files not set.');
    }

    public function getImageServerInfoUrl(Photos $photo): string
    {
        return $this->getImageServerBaseUrl() . $this->getEncodedIiifId($photo);

    }

    protected function getImageServerBaseUrl(): string
    {
        return $this->getKey('imageServerBaseUrl');
    }

    public function getZbarImageSize(): int
    {
        return $this->getKey('zbarImageHeight');
    }

    public function getPreviewSize(): int
    {
        return $this->getKey('previewImageSize');
    }

    public function getDatabotImageSize(): int
    {
        return $this->getKey('databotImageSite');
    }

    public function getPreviewQuality(): int
    {
        return $this->getKey('previewQuality');
    }

    public function getImageServerUrlThumbnail(Photos $photo): string
    {
        return $this->getImageServerBaseUrl() . $this->getEncodedIiifId($photo) . '/full/' . $this->getThumbnailSize() . ',/0/default.jpg';
    }

    public function getThumbnailSize(): int
    {
        return $this->getKey('thumbImageWidth');
    }

    public function createS3Jp2Name(Photos $photo): string
    {
        return $photo->getFullSpecimenId() . '_' . $photo->id . '.jp2';
    }

    public function createS3DatabotThumbName(Photos $photo): string
    {
        return $photo->getFullSpecimenId() . '_' . $photo->id . '.png';
    }

    public function createS3TifName(Photos $photo): string
    {
        return $photo->getFullSpecimenId() . '_' . $photo->id . '.tif';
    }

    protected function getEncodedIiifId(Photos $photo): string
    {
        $objectId = 's3://' . $this->getImageServerBucket($photo) . '/' . $photo->jp2Filename;
        return rawurlencode($objectId);

    }
}
