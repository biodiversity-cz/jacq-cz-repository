<?php declare(strict_types=1);

namespace App\Facades;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\EntityManager;
use App\Model\FileManagement\FileInsideCuratorBucket;
use App\Model\ImportStages\StageFactory;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\SpecimenIdService;
use League\Pipeline\Pipeline;
use Nette\Security\AuthenticationException;

readonly class CuratorFacade
{

    public function __construct(protected EntityManager $entityManager, protected S3Service $s3Service, protected StageFactory $stageFactory, protected RepositoryConfiguration $repositoryConfiguration, protected PhotoService $photoService, protected HerbariumService $herbariumService, protected SpecimenIdService $specimenIdService)
    {
    }

    /**
     * @return PhotosStatus[]
     */
    public function getAllStatuses(): array
    {
        return $this->entityManager->getPhotosStatusRepository()->findBy([], ['id' => 'ASC']);
    }

    /**
     * On curator request read curatorBucket and insert files basic info into the database
     */
    public function registerNewFiles(bool $useBarcode): CuratorFacade
    {
        foreach ($this->getEligibleCuratorBucketFiles($useBarcode) as $file) {
            $entity = new Photos();
            $entity
                ->setCreatedAt()
                ->setLastEditAt()
                ->setOriginalFilename($file->name)
                ->setStatus($this->photoService->getWaitingStatus())
                ->setHerbarium($this->herbariumService->getCurrentUserHerbarium())
                ->setArchiveFileSize($file->size)
                ->setUseBarcode($useBarcode);
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();

        return $this;
    }

    /**
     * @return FileInsideCuratorBucket[]
     */
    protected function getEligibleCuratorBucketFiles(bool $useBarcode): array
    {
        return array_filter($this->getAvailableCuratorBucketFiles($useBarcode), fn($item) => $item->isEligibleToBeImported() === true);
    }

    /**
     * @return FileInsideCuratorBucket[]
     */
    public function getAvailableCuratorBucketFiles(bool $useBarcode): array
    {
        $files = [];
        $unprocessedPhotos = $this->photoService->findUnprocessedPhotos($useBarcode);
        foreach ($this->s3Service->listObjects($this->herbariumService->getCurrentUserHerbarium()->getBucket()) as $filename) {
            if (!isset($unprocessedPhotos[$filename['Key']])) {
                $file = new FileInsideCuratorBucket($filename['Key'], (int)$filename['Size'], $filename['LastModified'], false, false, null, null);
            } else {
                $entity = $unprocessedPhotos[$filename['Key']];
                $alreadyWaiting = $entity->getStatus()->getId() === PhotosStatus::WAITING;
                $hasControlError = $entity->getStatus()->getId() === PhotosStatus::CONTROL_ERROR;
                $file = new FileInsideCuratorBucket($filename['Key'], (int)$filename['Size'], $filename['LastModified'], $alreadyWaiting, $hasControlError, $entity->getId(), $entity->getMessage());
            }
            if ($useBarcode) {
                if (!$this->specimenIdService->filenameFitsHerbariumPattern($file->name, $this->herbariumService->getCurrentUserHerbarium())) {
                    $file->setIneligibleForImport();
                }
            }
            $files[] = $file;
        }

        return $files;
    }

    public function importNewFilesByBarcode(): Pipeline
    {
        return (new Pipeline())
            ->pipe($this->stageFactory->createDownloadStage())
            ->pipe($this->stageFactory->createThumbnailStage())
            ->pipe($this->stageFactory->createMetadataStage())
            ->pipe($this->stageFactory->createBarcodeStage())
            ->pipe($this->stageFactory->createDuplicityStage())
            ->pipe($this->stageFactory->createConvertStage())
            ->pipe($this->stageFactory->createTransferStage());
    }

    public function importNewFilesByFilename(): Pipeline
    {
        return (new Pipeline())
            ->pipe($this->stageFactory->createDownloadStage())
            ->pipe($this->stageFactory->createFilenameStage())
            ->pipe($this->stageFactory->createThumbnailStage())
            ->pipe($this->stageFactory->createMetadataStage())
            ->pipe($this->stageFactory->createDuplicityStage())
            ->pipe($this->stageFactory->createConvertStage())
            ->pipe($this->stageFactory->createTransferStage());
    }

    /**
     * @return Photos[]
     */
    public function getOrphanedItems(): array
    {
        $photos = [];
        $dbItems = $this->entityManager->getPhotosRepository()->getOrphananble($this->herbariumService->getCurrentUserHerbarium());
        foreach ($dbItems as $photo) {
            if (!$this->s3Service->objectExists($this->herbariumService->getCurrentUserHerbarium()->getBucket(), $photo->getOriginalFilename())) {
                $photos[] = $photo;
            }
        }

        return $photos;
    }

    /**
     * @return Photos[]
     */
    public function getLatestImports(): array
    {
        return $this->photoService->findLastImported();
    }

    public function deleteNotImportedPhoto(Photos $photo): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium() === $photo->getHerbarium()) {
            $this->s3Service->deleteObject($photo->getHerbarium()->getBucket(), $photo->getOriginalFilename());
            $this->entityManager->remove($photo);
            $this->entityManager->flush();

            return $this;
        }

        throw new AuthenticationException('Not allowed to delete photo.');
    }

    public function deleteJustFile(string $filename): CuratorFacade
    {
        $this->s3Service->deleteObject($this->herbariumService->getCurrentUserHerbarium()->getBucket(), $filename);

        return $this;
    }

    public function reimportPhoto(Photos $photo, ?string $manualSpecimenId = null): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium() === $photo->getHerbarium()) {
            $photo
                ->setLastEditAt()
                ->setMessage(null)
                ->setSpecimenId($manualSpecimenId)
                ->setStatus($this->photoService->getWaitingStatus());
            $this->entityManager->flush();

            return $this;
        }

        throw new AuthenticationException('Not allowed to reimport photo.');
    }

    public function getArchiveFile(Photos $photo, string $destination): CuratorFacade
    {
        $this->s3Service->getObject($this->repositoryConfiguration->getArchiveBucket(), $photo->getArchiveFilename(), $destination);

        return $this;
    }

    public function getActualHerbarium(): Herbaria
    {
        return $this->herbariumService->getCurrentUserHerbarium();
    }
}
