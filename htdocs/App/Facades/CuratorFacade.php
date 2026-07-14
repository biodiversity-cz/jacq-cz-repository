<?php

declare(strict_types=1);

namespace App\Facades;

use App\Model\Database\Entity\Funding;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\Entity\PhotosType;
use App\Model\FileManagement\FileInsideCuratorBucket;
use App\Model\ImportStages\StageFactory;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;
use App\Services\Exceptions\ServiceException;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\SpecimenIdService;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use League\Pipeline\Pipeline;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

readonly class CuratorFacade
{
    public function __construct(protected EntityManagerInterface $entityManager, protected S3Service $s3Service, protected StageFactory $stageFactory, protected RepositoryConfiguration $repositoryConfiguration, protected PhotoService $photoService, protected HerbariumService $herbariumService, protected SpecimenIdService $specimenIdService)
    {
    }

    /**
     * @return PhotosStatus[]
     */
    public function getAllStatuses(): array
    {
        return $this->entityManager->getRepository(PhotosStatus::class)->findPairs('id', 'name');
    }

    /**
     * @return PhotosStatus[]
     */
    public function getPassedStatuses(): array
    {
        return $this->entityManager->getRepository(PhotosStatus::class)->findPairs('id', 'name', ['id' => PhotosStatus::PASSED]);
    }

    /**
     * @return mixed[]
     */
    public function getAllPhotoTypes(): array
    {
        return $this->entityManager->getRepository(PhotosType::class)->findPairs('id', 'name');
    }

    public function getAllAvailableFundings(User $user): array
    {
        $fundings = $this->entityManager->getRepository(Funding::class)->findAllAvailableActive($user);
        $pairs = [];
        foreach ($fundings as $entity) {
            $pairs[$entity->id] = $entity->name;
        }

        return $pairs;
    }

    /**
     * On curator request read curatorBucket and insert files basic info into the database.
     *
     * @param mixed[] $formData
     */
    public function registerNewFiles(User $user, array $formData): CuratorFacade
    {
        foreach ($this->getEligibleCuratorBucketFiles($user) as $file) {
            $entity = new Photos();
            $entity
                ->setOriginalFilename($file->name)
                ->setStatus($this->photoService->getWaitingStatus())
                ->setHerbarium($this->herbariumService->getCurrentUserHerbarium($user))
                ->setArchiveFileSize($file->size)
                ->setBucketSuffix($this->repositoryConfiguration->getRecentlyUsedBucketSuffix())
                ->setType($this->entityManager->getReference(PhotosType::class, $formData['photoType']))
                ->setCreatedAt()
                ->setLastEditAt();
            if (!empty($formData['funding'])) {
                $funding = $this->entityManager->getRepository(Funding::class)->findAssignable($formData['funding'], $user);
                $entity->setFunding($funding);
            }
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();

        return $this;
    }

    /**
     * @return FileInsideCuratorBucket[]
     */
    protected function getEligibleCuratorBucketFiles(User $user): array
    {
        return array_filter($this->getAvailableCuratorBucketFiles($user), fn ($item) => true === $item->isEligibleToBeImported());
    }

    /**
     * @return FileInsideCuratorBucket[]
     */
    public function getAvailableCuratorBucketFiles(User $user): array
    {
        $files = [];
        $unprocessedPhotos = $this->photoService->findUnprocessedPhotos($user);
        $minFileSize = $this->herbariumService->getCurrentUserHerbarium($user)->minimalFileSize;

        foreach ($this->s3Service->listObjects($this->herbariumService->getCurrentUserHerbarium($user)->bucket) as $filename) {
            if (!isset($unprocessedPhotos[$filename['Key']])) {
                $file = new FileInsideCuratorBucket($filename['Key'], (int) $filename['Size'], $minFileSize, $filename['LastModified'], false, false, null, null);
            } else {
                $entity = $unprocessedPhotos[$filename['Key']];
                $alreadyWaiting = PhotosStatus::WAITING === $entity->status->id;
                $hasControlError = PhotosStatus::IMAGE_CONTROL_ERROR === $entity->status->id;
                $file = new FileInsideCuratorBucket($filename['Key'], (int) $filename['Size'], $minFileSize, $filename['LastModified'], $alreadyWaiting, $hasControlError, $entity->id, $entity->error?->message);
            }

            $files[] = $file;
        }

        return $files;
    }

    public function importNewFilesPipeline(): Pipeline
    {
        return new Pipeline()
            ->pipe($this->stageFactory->createDownloadStage())
            ->pipe($this->stageFactory->createThumbnailStage())
            ->pipe($this->stageFactory->createMetadataStage())
            ->pipe($this->stageFactory->createBarcodeStage())
            ->pipe($this->stageFactory->createDuplicityStage())
            ->pipe($this->stageFactory->createTransferStage());
    }

    public function importMultiplierPipeline(): Pipeline
    {
        return new Pipeline()
            ->pipe($this->stageFactory->createThumbnailStage()) // to generate thumb into ImportError just for case of error..
            ->pipe($this->stageFactory->createDuplicityStage())
            ->pipe($this->stageFactory->createTransferStage());
    }

    public function publishPhotoPipeline(): Pipeline
    {
        return new Pipeline()
            ->pipe($this->stageFactory->createDownloadFromMasterStage())
            ->pipe($this->stageFactory->createConvertStage())
            ->pipe($this->stageFactory->createPidStage())
            ->pipe($this->stageFactory->createChecksumStage())
            ->pipe($this->stageFactory->createSolrStage())
            ->pipe($this->stageFactory->createTransferJp2Stage())
            ->pipe($this->stageFactory->createCleanupTempFilesStage());
    }

    public function importCleanupPipeline(): Pipeline
    {
        return new Pipeline()
            ->pipe($this->stageFactory->createCleanupTempFilesStage())
            ->pipe($this->stageFactory->createCleanupCuratorBucketStage());
    }

    /**
     * @return Photos[]
     */
    public function multiplyPhotos(Photos $originalPhoto): array
    {
        $newItems = [];
        foreach ($originalPhoto->multiplier->barcodes as $barcode) {
            $copy = new Photos();
            $copy->addImportError();
            $copy->setOriginalFilename($originalPhoto->originalFilename)
                ->setOriginalFileAt($originalPhoto->originalFileTimestamp)
                ->setHerbarium($originalPhoto->herbarium)
                ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::IMAGE_CONTROL_ERROR))
                ->setSpecimenId($barcode)
                ->setWidth($originalPhoto->width)
                ->setHeight($originalPhoto->height)
                ->setArchiveFileSize($originalPhoto->archiveFileSize)
                ->setJp2FileSize($originalPhoto->JP2FileSize)
                ->setExif($originalPhoto->exif)
                ->setIdentify($originalPhoto->identify)
                ->setType($originalPhoto->type)
                ->setBucketSuffix($originalPhoto->bucketSuffix)
                ->setCreatedAt()
                ->setLastEditAt();

            $this->entityManager->persist($copy);
            $newItems[] = $copy;
        }
        $originalPhoto->removeMultiplier();

        $this->entityManager->flush();

        return $newItems;
    }

    /**
     * @return Photos[]
     */
    public function getOrphanedItems(User $user): array
    {
        $dbItems = $this->entityManager->getRepository(Photos::class)->getOrphanable($user);

        $existingFiles = $this->s3Service->listObjectsNamesOnly($this->herbariumService->getCurrentUserHerbarium($user)->bucket);
        $existingFilesSet = array_flip($existingFiles); // For O(1) lookup

        $orphaned = [];
        foreach ($dbItems as $photo) {
            if (!isset($existingFilesSet[$photo->originalFilename])) {
                $orphaned[] = $photo;
            }
        }

        return $orphaned;
    }

    public function deletePhoto(User $user, Photos $entity): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium($user) !== $entity->herbarium) {
            throw new AuthenticationException('Not allowed to delete photo.');
        }

        try {
            $this->entityManager->beginTransaction();

            $lockedEntity = $this->entityManager
                ->createQueryBuilder()
                ->select('p')
                ->from(Photos::class, 'p')
                ->where('p.id = :id')
                ->setParameter('id', $entity->id)
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getSingleResult();

            // Determine which files need to be deleted based on status
            $filesToDelete = [];
            switch ($lockedEntity->status->id) {
                case PhotosStatus::WAITING:
                case PhotosStatus::IMAGE_CONTROL_ERROR:
                    $filesToDelete = [
                        ['bucket' => $lockedEntity->herbarium->bucket, 'key' => $lockedEntity->originalFilename],
                    ];
                    break;
                case PhotosStatus::IMAGE_CONTROL_OK:
                case PhotosStatus::SPECIMEN_CONTROL_OK:
                case PhotosStatus::EMBARGO:
                case PhotosStatus::DEVELOP_PROCEED:
                    $filesToDelete = [
                        ['bucket' => $this->repositoryConfiguration->getArchiveBucket($lockedEntity), 'key' => $lockedEntity->archiveFilename],
                        ['bucket' => $this->repositoryConfiguration->getDatabotThumbsBucket($lockedEntity), 'key' => $lockedEntity->databotThumbFilename],
                        ['bucket' => $this->repositoryConfiguration->getImageServerBucket($lockedEntity), 'key' => $lockedEntity->jp2Filename],
                    ];
                    break;
                default:
                    throw new ServiceException('This photo cannot be deleted');
            }

            // First: Remove from database (database is source of truth)
            $this->entityManager->remove($lockedEntity);
            $this->entityManager->flush();
            $this->entityManager->commit();

            // Second: Delete files from S3 (outside transaction)
            // If this fails, the DB record is already gone and orphaned files can be cleaned up later
            foreach ($filesToDelete as $file) {
                if (null === $file['key']) {
                    continue;
                }
                $this->s3Service->deleteObject($file['bucket'], $file['key']);
            }
        } catch (\Throwable $e) {
            $this->entityManager->rollback();
            throw new ServiceException('Error in photo delete: '.$e->getMessage(), previous: $e);
        }

        return $this;
    }

    public function addEmbargoPhoto(User $user, Photos $entity): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium($user) !== $entity->herbarium) {
            throw new AuthenticationException('Not allowed to edit photo.');
        }

        try {
            $this->entityManager->beginTransaction();
            /** @var Photos $lockedEntity */
            $lockedEntity = $this->entityManager
                ->createQueryBuilder()
                ->select('p')
                ->from(Photos::class, 'p')
                ->where('p.id = :id')
                ->setParameter('id', $entity->id)
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getSingleResult();

            if (in_array($lockedEntity->status->id, PhotosStatus::EMBARGOABLE)) {
                $lockedEntity
                    ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::EMBARGO))
                    ->setEmbargoTimeout()
                    ->setLastEditAt();
            } else {
                throw new ServiceException('This photo cannot be edited.');
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            throw new ServiceException('Error in photo delete: '.$e->getMessage());
        }

        return $this;
    }

    public function dropEmbargoPhoto(User $user, Photos $entity): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium($user) !== $entity->herbarium) {
            throw new AuthenticationException('Not allowed to edit photo.');
        }

        try {
            $this->entityManager->beginTransaction();
            /** @var Photos $lockedEntity */
            $lockedEntity = $this->entityManager
                ->createQueryBuilder()
                ->select('p')
                ->from(Photos::class, 'p')
                ->where('p.id = :id')
                ->setParameter('id', $entity->id)
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getSingleResult();

            if (PhotosStatus::EMBARGO == $lockedEntity->status->id) {
                $lockedEntity
                    ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::SPECIMEN_CONTROL_OK))
                    ->dropEmbargoTimeout()
                    ->setLastEditAt();
            } else {
                throw new ServiceException('This photo cannot be edited.');
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            throw new ServiceException('Error in photo delete: '.$e->getMessage());
        }

        return $this;
    }

    public function deleteJustNotimportedFile(User $user, string $filename): CuratorFacade
    {
        $this->s3Service->deleteObject($this->herbariumService->getCurrentUserHerbarium($user)->bucket, $filename);

        return $this;
    }

    public function reimportPhoto(User $user, Photos $photo, ?string $manualSpecimenId = null): CuratorFacade
    {
        if (!$this->specimenIdService->isValid($manualSpecimenId)) {
            throw new ServiceException('Specimen ID is not valid.');
        }
        if ($this->herbariumService->getCurrentUserHerbarium($user) === $photo->herbarium) {
            if (null !== $photo->error) {
                $this->entityManager->remove($photo->error);
                $photo->removeImportError();
                $photo
                    ->setLastEditAt()
                    ->setSpecimenId($manualSpecimenId)
                    ->setStatus($this->photoService->getWaitingStatus());
                $this->entityManager->flush();

                return $this;
            }
        }

        throw new AuthenticationException('Not allowed to reimport photo.');
    }

    public function getArchiveFile(Photos $photo, string $destination): CuratorFacade
    {
        $this->s3Service->getObject($this->repositoryConfiguration->getArchiveBucket($photo), $photo->archiveFilename, $destination);

        return $this;
    }

    public function expireEmbargo(): self
    {
        $query = $this->entityManager->createQuery(
            'UPDATE App\Model\Database\Entity\Photos e SET e.status = :newStatus, e.lastEdit = CURRENT_TIMESTAMP(), e.embargoTimeout = NULL  WHERE e.status = :oldStatus AND  e.embargoTimeout < CURRENT_TIMESTAMP()'
        );
        $query->setParameter('newStatus', PhotosStatus::SPECIMEN_CONTROL_OK);
        $query->setParameter('oldStatus', PhotosStatus::EMBARGO);

        $query->execute();

        return $this;
    }

    /**
     * Mark all publishable photos (status = SPECIMEN_CONTROL_OK) as WAITING_FOR_PUBLISHING
     * Uses the same criteria as getPublishablePhotosDatasource() for consistency
     * Executes as a single bulk UPDATE query - no entity loading overhead.
     */
    public function markPublishable(User $user): self
    {
        $this->photoService->markAllPublishableAsWaitingForPublishing($user);

        return $this;
    }
}
