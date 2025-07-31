<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Services\AppConfiguration;
use App\Services\EntityServices\PhotoService;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\SpecimenIdService;
use App\Services\TempDir;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\LinkGenerator;

readonly class StageFactory
{

    public function __construct(protected S3Service $s3Service, protected TempDir $tempDir, protected EntityManagerInterface $entityManager, protected RepositoryConfiguration $repositoryConfiguration, protected ImagickService $imageService, protected LinkGenerator $linkGenerator, protected PhotoService $photoService, protected AppConfiguration $appConfiguration, protected SpecimenIdService $specimenIdService)
    {
    }

    public function createDownloadStage(): DownloadStage
    {
        return new DownloadStage($this->s3Service, $this->repositoryConfiguration);
    }

    public function createBarcodeStage(): BarcodeStage
    {
        return new BarcodeStage($this->repositoryConfiguration, $this->imageService);
    }

    public function createMetadataStage(): MetadataStage
    {
        return new MetadataStage($this->repositoryConfiguration, $this->imageService);
    }

    public function createThumbnailStage(): ThumbnailStage
    {
        return new ThumbnailStage($this->tempDir, $this->repositoryConfiguration, $this->imageService);
    }

    public function createConvertStage(): ConvertStage
    {
        return new ConvertStage($this->s3Service, $this->repositoryConfiguration, $this->imageService);
    }

    public function createDuplicityStage(): DuplicityStage
    {
        return new DuplicityStage($this->photoService, $this->linkGenerator, $this->imageService, $this->repositoryConfiguration, $this->s3Service);
    }

    public function createTransferStage(): TransferStage
    {
        return new TransferStage($this->tempDir, $this->s3Service, $this->repositoryConfiguration, $this->appConfiguration);
    }

}
