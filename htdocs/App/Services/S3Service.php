<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\S3Exception;
use Aws\Result;
use Aws\S3\S3Client;
use Psr\Http\Message\StreamInterface;

readonly class S3Service
{
    public function __construct(protected S3Client $s3)
    {
    }

    public function objectExists(string $bucket, string $object): bool
    {
        return $this->s3->doesObjectExist($bucket, $object);
    }

    public function putTiffIfNotExists(string $bucket, string $key, string $path): Result
    {
        return $this->putFileIfNotExists($bucket, $key, $path, 'image/tiff');
    }

    public function putFileIfNotExists(string $bucket, string $key, string $path, string $contentType): Result
    {
        if ($this->s3->doesObjectExist($bucket, $key)) {
            throw new S3Exception(sprintf('file %s already exists', $key));
        }

        $result = $this->s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SourceFile' => $path,
            'ContentType' => $contentType,
        ]);

        $localSize = filesize($path);
        $s3Size = $this->getObjectSize($bucket, $key) ?? 0;

        if ($localSize !== $s3Size) {
            $this->s3->deleteObject(['Bucket' => $bucket, 'Key' => $key]);
            throw new S3Exception(sprintf('Uploaded file size mismatch for %s', $key));
        }

        return $result;
    }

    public function getObjectSize(string $bucket, string $key): int
    {
        $result = $this->s3->headObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);

        return $result['ContentLength'];
    }

    public function headObject(string $bucket, string $key): Result
    {
        return $this->s3->headObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
    }

    public function deleteObject(string $bucket, string $key): Result
    {
        return $this->s3->deleteObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
    }

    public function deleteBucket(string $bucket): Result
    {
        return $this->s3->deleteBucket(['Bucket' => $bucket]);
    }

    public function createBucket(string $bucket): Result
    {
        return $this->s3->createBucket(['Bucket' => $bucket]);
    }

    public function doesBucketExist(string $bucket): bool
    {
        return $this->s3->doesBucketExist($bucket);
    }

    public function getObjectOriginalTimestamp(string $bucket, string $key): ?\DateTimeImmutable
    {
        $result = $this->s3->headObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);
        $data = $result->get('Metadata');
        if (isset($data['origin-date-iso8601'])) {
            return new \DateTimeImmutable($data['origin-date-iso8601']);
        }

        return null;
    }

    public function copyObject(string $source, string $targetBucket, string $targetFilename, string $contentType): Result
    {
        return $this->s3->copyObject([
            'Bucket' => $targetBucket,
            'Key' => $targetFilename,
            'CopySource' => $source, // bucket/key
            'ContentType' => $contentType,
        ]);
    }

    public function putJp2IfNotExists(string $bucket, string $key, string $path): Result
    {
        return $this->putFileIfNotExists($bucket, $key, $path, 'image/jp2');
    }

    public function putPngIfNotExists(string $bucket, string $key, string $path): Result
    {
        return $this->putFileIfNotExists($bucket, $key, $path, 'image/png');
    }

    public function getObject(string $bucket, string $key, string $path): Result
    {
        return $this->s3->getObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'SaveAs' => $path]);
    }

    /**
     * @return string[]
     */
    public function listObjectsNamesOnly(string $bucket): array
    {
        $objects = [];
        $result = $this->s3->getIterator('ListObjects', [
            'Bucket' => $bucket,
            // "Prefix" => 'some_folder/'
        ]);
        foreach ($result as $object) {
            $objects[] = $object['Key'];
        }

        return $objects;
    }

    public function listObjects(string $bucket): \Iterator
    {
        return $this->s3->getIterator('ListObjects', [
            'Bucket' => $bucket,
            // "Prefix" => 'some_folder/'
        ]);
    }

    public function getStreamOfObject(string $bucket, string $key): mixed
    {
        $this->s3->registerStreamWrapper();

        return fopen(sprintf('s3://%s/%s', $bucket, $key), 'r');
    }

    public function getPsrStreamOfObject(string $bucket, string $key): StreamInterface
    {
        $result = $this->s3->getObject([
            'Bucket' => $bucket,
            'Key' => $key,
        ]);

        return $result['Body'];
    }
}
