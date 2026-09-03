<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Services\AppConfiguration;
use App\Services\EntityServices\PhotoService;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\Solr\SolrClientService;
use App\Services\SpecimenIdService;
use App\Services\TempDir;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\LinkGenerator;

readonly class StageFactory
{
    public function __construct(protected S3Service $s3Service, protected TempDir $tempDir, protected EntityManagerInterface $entityManager, protected RepositoryConfiguration $repositoryConfiguration, protected ImagickService $imagickService, protected LinkGenerator $linkGenerator, protected PhotoService $photoService, protected AppConfiguration $appConfiguration, protected SpecimenIdService $specimenIdService, protected SolrClientService $solrClientService)
    {
    }

    public function createDownloadStage(): DownloadStage
    {
        return new DownloadStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService, $this->s3Service);
    }

    public function createDownloadFromMasterStage(): DownloadFromMasterStage
    {
        return new DownloadFromMasterStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService, $this->s3Service);
    }

    public function createBarcodeStage(): BarcodeStage
    {
        return new BarcodeStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService, $this->specimenIdService);
    }

    public function createMetadataStage(): MetadataStage
    {
        return new MetadataStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService);
    }

    public function createThumbnailStage(): ThumbnailStage
    {
        return new ThumbnailStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService);
    }

    public function createConvertStage(): ConvertStage
    {
        return new ConvertStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService);
    }

    public function createDuplicityStage(): DuplicityStage
    {
        return new DuplicityStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService, $this->photoService, $this->linkGenerator, $this->s3Service);
    }

    public function createTransferStage(): TransferStage
    {
        return new TransferStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService, $this->appConfiguration, $this->s3Service);
    }

    public function createTransferJp2Stage(): TransferJp2Stage
    {
        return new TransferJp2Stage($this->tempDir, $this->repositoryConfiguration, $this->imagickService, $this->appConfiguration, $this->s3Service);
    }

    public function createCleanupTempFilesStage(): CleanupTempFilesStage
    {
        return new CleanupTempFilesStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService);
    }

    public function createCleanupCuratorBucketStage(): CleanCuratorBucketStage
    {
        return new CleanCuratorBucketStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService, $this->s3Service, $this->appConfiguration);
    }

    public function createPidStage(): PidStage
    {
        return new PidStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService, $this->specimenIdService);
    }

    public function createChecksumStage(): ChecksumStage
    {
        return new ChecksumStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService);
    }

    public function createSolrStage(): SolrStage
    {
        return new SolrStage($this->tempDir, $this->repositoryConfiguration, $this->imagickService, $this->solrClientService, $this->entityManager);
    }
}
