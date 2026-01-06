<?php declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ConfigurationException;
use App\Model\Database\Entity\Photos;

final readonly class RepositoryConfiguration
{

    public const string SPECIMEN_NUMERIC_FORMAT = '%07d';

    /**
     * @param mixed[] $config
     */
    public function __construct(protected array $config, protected TempDir $tempDir)
    {
    }

    public function getRepositoryArchiveBucket(): string
    {
        return $this->getKey('archiveBucket', 'Archive bucket not set.');
    }

    public function getArchiveBucketPrefix(): string
    {
        return $this->getKey('archiveBucketPrefix', 'Archive bucket not set.');
    }

    public function getRecentArchiveBucket(): string
    {
        return $this->getArchiveBucketPrefix().$this->getRecentBucketSuffix();
    }

    public function getRecentBucketSuffix(): string
    {
        return $this->getKey('recentBucketSuffix');
    }

    protected function getKey(string $key, string $msg = ''): mixed
    {
        if (!isset($this->config[$key])) {
            $text = $msg === '' ? 'Configuration parameter ' . strtoupper($key) . ' not set!' : $msg;

            throw new ConfigurationException($text);
        }

        return $this->config[$key];
    }

    public function getRepositoryImageServerBucket(): string
    {
        return $this->getKey('jp2Bucket', 'Image server bucket not set.');
    }

    public function getRepositoryDatabotThumbsBucket(): string
    {
        return $this->getKey('thumbBucket', 'Thumbs bucket not set.');
    }

    public function getJp2Quality(): int
    {
        return $this->getKey('jp2Quality', 'Compression for image server files not set.');
    }

    public function getImageServerInfoUrl(string $jp2ObjectName): string
    {
        return $this->getImageServerBaseUrl() .$this->getEncodedIiifId($jp2ObjectName);

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

    public function getImageServerUrlThumbnail(string $jp2ObjectName): string
    {
        return $this->getImageServerBaseUrl() . $this->getEncodedIiifId($jp2ObjectName) . '/full/' . $this->getThumbnailSize() . ',/0/default.jpg';
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

    protected function getEncodedIiifId(string $name): string
    {
        $objectId = 's3://' . $this->getRepositoryImageServerBucket() . '/' . $name;
        return rawurlencode($objectId);

    }
}
