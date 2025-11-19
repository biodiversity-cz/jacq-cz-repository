<?php declare(strict_types=1);

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
use Nette\Neon\Exception;
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
            $pairs[$entity->getId()] = $entity->getName();
        }

        return $pairs;
    }

    /**
     * On curator request read curatorBucket and insert files basic info into the database
     *
     * @param mixed[] $formData
     */
    public function registerNewFiles(User $user, array $formData): CuratorFacade
    {
        foreach ($this->getEligibleCuratorBucketFiles($user) as $file) {
            $entity = new Photos();
            $entity
                ->setCreatedAt()
                ->setLastEditAt()
                ->setOriginalFilename($file->name)
                ->setStatus($this->photoService->getWaitingStatus())
                ->setHerbarium($this->herbariumService->getCurrentUserHerbarium($user))
                ->setArchiveFileSize($file->size)
                ->setType($this->entityManager->getReference(PhotosType::class, $formData['photoType']));
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
        return array_filter($this->getAvailableCuratorBucketFiles($user), fn($item) => $item->isEligibleToBeImported() === true);
    }

    /**
     * @return FileInsideCuratorBucket[]
     */
    public function getAvailableCuratorBucketFiles(User $user): array
    {
        $files = [];
        $unprocessedPhotos = $this->photoService->findUnprocessedPhotos($user);
        foreach ($this->s3Service->listObjects($this->herbariumService->getCurrentUserHerbarium($user)->getBucket()) as $filename) {
            if (!isset($unprocessedPhotos[$filename['Key']])) {
                $file = new FileInsideCuratorBucket($filename['Key'], (int)$filename['Size'], $filename['LastModified'], false, false, null, null);
            } else {
                $entity = $unprocessedPhotos[$filename['Key']];
                $alreadyWaiting = $entity->getStatus()->getId() === PhotosStatus::WAITING;
                $hasControlError = $entity->getStatus()->getId() === PhotosStatus::IMAGE_CONTROL_ERROR;
                $file = new FileInsideCuratorBucket($filename['Key'], (int)$filename['Size'], $filename['LastModified'], $alreadyWaiting, $hasControlError, $entity->getId(), $entity->getError()?->getMessage());
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
            ->pipe($this->stageFactory->createConvertStage())
            ->pipe($this->stageFactory->createTransferStage());
    }

    public function importMultiplierPipeline(): Pipeline
    {
        return new Pipeline()
            ->pipe($this->stageFactory->createThumbnailStage()) //to generate thumb into ImportError just for case of error..
            ->pipe($this->stageFactory->createDuplicityStage())
            ->pipe($this->stageFactory->createTransferStage());
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
        foreach ($originalPhoto->getMultiplier()->getBarcodes() as $barcode) {

            $copy = new Photos();
            $copy->addImportError();
            $copy->setOriginalFilename($originalPhoto->getOriginalFilename())
                ->setOriginalFileAt($originalPhoto->getOriginalFileAt())
                ->setHerbarium($originalPhoto->getHerbarium())
                ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::IMAGE_CONTROL_ERROR))
                ->setSpecimenId($barcode)
                ->setWidth($originalPhoto->getWidth())
                ->setHeight($originalPhoto->getHeight())
                ->setArchiveFileSize($originalPhoto->getArchiveFileSize())
                ->setJp2FileSize($originalPhoto->getJp2FileSize())
                ->setExif($originalPhoto->getExif())
                ->setIdentify($originalPhoto->getIdentify())
                ->setType($originalPhoto->getType())
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
        $photos = [];
        $dbItems = $this->entityManager->getRepository(Photos::class)->getOrphanable($user);
        foreach ($dbItems as $photo) {
            if (!$this->s3Service->objectExists($this->herbariumService->getCurrentUserHerbarium($user)->getBucket(), $photo->getOriginalFilename())) {
                $photos[] = $photo;
            }
        }

        return $photos;
    }

    public function deletePhoto(User $user, Photos $entity): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium($user) !== $entity->getHerbarium()) {
            throw new AuthenticationException('Not allowed to delete photo.');
        }

        try {
            $this->entityManager->beginTransaction();

            $lockedEntity = $this->entityManager
                ->createQueryBuilder()
                ->select('p')
                ->from(Photos::class, 'p')
                ->where('p.id = :id')
                ->setParameter('id', $entity->getId())
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getSingleResult();

            switch ($lockedEntity->getStatus()->getId()) {
                case PhotosStatus::WAITING:
                case PhotosStatus::IMAGE_CONTROL_ERROR:
                    $this->s3Service->deleteObject($lockedEntity->getHerbarium()->getBucket(), $lockedEntity->getOriginalFilename());
                    break;
                case PhotosStatus::IMAGE_CONTROL_OK:
                case PhotosStatus::SPECIMEN_CONTROL_OK:
                case PhotosStatus::EMBARGO:
                case PhotosStatus::DEVELOP_PROCEED:
                    $this->s3Service->deleteObject($this->repositoryConfiguration->getRepositoryImageServerBucket(), $lockedEntity->getJp2Filename());
                    $this->s3Service->deleteObject($this->repositoryConfiguration->getRepositoryArchiveBucket(), $lockedEntity->getArchiveFilename());
                    $this->s3Service->deleteObject($this->repositoryConfiguration->getRepositoryDatabotThumbsBucket(), $lockedEntity->getDatabotThumbFilename());
                    break;
                default:
                    throw new ServiceException('This photo cannot be deleted');

            }

            $this->entityManager->remove($lockedEntity);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            throw new ServiceException('Error in photo delete: ' . $e->getMessage());
        }

        return $this;
    }

    public function addEmbargoPhoto(User $user, Photos $entity): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium($user) !== $entity->getHerbarium()) {
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
                ->setParameter('id', $entity->getId())
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getSingleResult();

            if (in_array($lockedEntity->getStatus()->getId(), PhotosStatus::EMBARGOABLE)) {
                $lockedEntity
                    ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::EMBARGO))
                    ->setEmbargoTimeout()
                    ->setLastEditAt();
            }else{
                throw new ServiceException('This photo cannot be edited.');
            }


            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            throw new ServiceException('Error in photo delete: ' . $e->getMessage());
        }

        return $this;
    }

    public function dropEmbargoPhoto(User $user, Photos $entity): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium($user) !== $entity->getHerbarium()) {
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
                ->setParameter('id', $entity->getId())
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getSingleResult();

            if ($lockedEntity->getStatus()->getId() == PhotosStatus::EMBARGO) {
                $lockedEntity
                    ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::SPECIMEN_CONTROL_OK))
                    ->dropEmbargoTimeout()
                    ->setLastEditAt();
            }else{
                throw new ServiceException('This photo cannot be edited.');
            }


            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Throwable $e) {
            $this->entityManager->rollback();

            throw new ServiceException('Error in photo delete: ' . $e->getMessage());
        }

        return $this;
    }


    public function deleteJustNotimportedFile(User $user, string $filename): CuratorFacade
    {
        $this->s3Service->deleteObject($this->herbariumService->getCurrentUserHerbarium($user)->getBucket(), $filename);
        return $this;
    }

    public function reimportPhoto(User $user, Photos $photo, ?string $manualSpecimenId = null): CuratorFacade
    {
        if ($this->herbariumService->getCurrentUserHerbarium($user) === $photo->getHerbarium()) {
            if ($photo->getError() !== null) {
                $this->entityManager->remove($photo->getError());
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
        $this->s3Service->getObject($this->repositoryConfiguration->getRepositoryArchiveBucket(), $photo->getArchiveFilename(), $destination);

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

    public function markPublishable(User $user): self
    {
        $result = $this->photoService->getPublishablePhotosDatasource($user)->getQuery()->getResult();
        foreach ($result as $photo) {
            $photo
                ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::WAITING_FOR_PUBLISHING))
                ->setLastEditAt();
        }
        $this->entityManager->flush();
        return $this;

    }

}
