<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\TransferStageException;
use App\Services\AppConfiguration;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use League\Pipeline\StageInterface;

class TransferStage extends BaseStage implements StageInterface
{

    public function __construct(TempDir $tempDir, RepositoryConfiguration $repositoryConfiguration, ImagickService $imagickService, protected readonly AppConfiguration $appConfiguration, protected readonly S3Service $s3Service)
    {
        parent::__construct($tempDir, $repositoryConfiguration, $imagickService);
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        $this->uploadJp2toRepository();
        $this->uploadTiftoRepository();
        $this->uploadDatabotThumbToRepository();

        return $payload;
    }

    protected function uploadJp2toRepository(): void
    {
        try {
            $this->s3Service->putJp2IfNotExists(
                $this->repositoryConfiguration->getRepositoryImageServerBucket(),
                $this->repositoryConfiguration->createS3Jp2Name($this->item),
                $this->getIiifTempPath());
            $this->item->setJP2Filename($this->repositoryConfiguration->createS3Jp2Name($this->item));
        } catch (\Throwable $exception) {
            throw new TransferStageException('jp2 upload error (' . $exception->getMessage() . ')');
        }
    }

    protected function uploadTiftoRepository(): void
    {
        try {
            $this->s3Service->putTiffIfNotExists(
                $this->repositoryConfiguration->getRepositoryArchiveBucket(),
                $this->repositoryConfiguration->createS3TifName($this->item),
                $this->getMasterTempPath());
            $this->item->setArchiveFilename($this->repositoryConfiguration->createS3TifName($this->item));
        } catch (\Throwable $exception) {
            throw new TransferStageException('tiff upload error (' . $exception->getMessage() . ')');
        }
    }

    protected function uploadDatabotThumbToRepository(): void
    {
        try {
            $this->s3Service->putPngIfNotExists(
                $this->repositoryConfiguration->getRepositoryDatabotThumbsBucket(),
                $this->repositoryConfiguration->createS3DatabotThumbName($this->item),
                $this->getDatabotThumbTempPath());
            $this->item->setDatabotThumbFilename($this->repositoryConfiguration->createS3DatabotThumbName($this->item));
        } catch (\Throwable $exception) {
            throw new TransferStageException('databot png upload error (' . $exception->getMessage() . ')');
        }
    }

}
