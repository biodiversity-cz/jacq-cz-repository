<?php declare(strict_types=1);

namespace App\Console\Scheduled;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\ImportStages\Exceptions\DuplicityStageException;
use App\Model\ImportStages\Exceptions\ImportStageException;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProceedCuratorImage extends Command
{

    public const int LIMIT = 4;

    /**
     * Running as a CronJob - process images from curatorBucket to the repository waiting room
     */
    public function __construct(protected readonly EntityManagerInterface $entityManager, protected readonly RepositoryConfiguration $storageConfiguration, protected readonly S3Service $s3Service, protected readonly CuratorFacade $curatorFacade, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('curator:importImage');
        $this->setDescription(sprintf('take %c image(s) from curator bucket and proceed import', self::LIMIT));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        for ($i = 0; $i < self::LIMIT; $i++) {
            //mainPhoto
            try {
                $photoProcessed = $this->proceedMainPhoto($output);
            } catch (ImportStageException $e) {
                $output->writeln("\n" . $e->getMessage());
                return Command::FAILURE;
            }
            if ($photoProcessed === null) {
                continue;
            }
            //multiply when needed
            if ($photoProcessed->getHerbarium()->hasMultipleBarcodeMultiplier() && !empty($photoProcessed->getMultiplier()?->getBarcodes())) {
                try {
                    $this->proceedMultiplier($output, $photoProcessed);
                } catch (ImportStageException $e) {
                    $output->writeln("\n" . $e->getMessage());
                    return Command::FAILURE;
                }
            }
            //clean individual Photo run
            try {
                $this->curatorFacade->importCleanupPipeline()->process($photoProcessed);
            } catch (\Throwable $e) {
                $output->writeln("\n" . $e->getMessage());
                return Command::FAILURE;
            }
        }

        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));

        return Command::SUCCESS;
    }

    protected function proceedMainPhoto(OutputInterface $output): ?Photos
    {
        $this->entityManager->getConnection()->beginTransaction(); //we are locking the selected row
        $photo = $this->getPhoto();
        if ($photo === null) {
            $this->entityManager->getConnection()->rollBack();

            return null;
        }

        try {
            $output->write("\n filename: s3://" . $photo->getHerbarium()->getBucket() . '/' . $photo->getOriginalFilename() . "\n");
            $photo = $this->prepareImportMessagesStorage($photo);

            $this->curatorFacade->importNewFilesPipeline()->process($photo);
            $photo->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::IMAGE_CONTROL_OK));
            $this->entityManager->remove($photo->getError());
            $photo->removeImportError();
        } catch (ImportStageException $e) {
            $photo->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::IMAGE_CONTROL_ERROR));
            $photo->getError()->setMessage($e->getMessage());
            $output->write("\n ERROR: " . $e->getMessage() . "\n");
            //mainPhoto did not succeeded,
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
            return null;
        } catch (\Throwable $e) {
            $this->entityManager->getConnection()->rollBack();
            $output->write("\n ERROR: " . $e->getMessage() . "\n");
            throw new ImportStageException($e->getMessage());
        }

        $this->entityManager->flush();
        $this->entityManager->getConnection()->commit();

        return $photo;
    }

    protected function getPhoto(): ?Photos
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id = ? ORDER BY id asc FOR UPDATE SKIP LOCKED LIMIT 1 ', $rsm);
        $query->setParameter(1, PhotosStatus::WAITING);
        try {
            /** @var Photos $photo */
            $photo = $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }

        return $photo;
    }

    protected function prepareImportMessagesStorage(Photos $photo): Photos
    {
        $photo->setLastEditAt();

        //remove from potential previous run
        $photo->removeImportError();
        $photo->removeMultiplier();

        $photo->addImportError()->setMessage('');
        $this->entityManager->persist($photo);

        return $photo;
    }

    protected function proceedMultiplier(OutputInterface $output, Photos $mainPhoto): ?Photos
    {
        $this->entityManager->getConnection()->beginTransaction();
        $newItems = $this->curatorFacade->multiplyPhotos($mainPhoto);

        foreach ($newItems as $newItem) {
            try {
                $output->write("\n multiply from ID " . $mainPhoto->getId() . " into ID " . $newItem->getId() . "\n");
                $this->curatorFacade->importMultiplierPipeline()->process($newItem);
                $newItem->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::IMAGE_CONTROL_OK));
                $this->entityManager->remove($newItem->getError());
                $newItem->removeImportError();
            } catch (DuplicityStageException $e) {
                $this->entityManager->remove($newItem);
                $output->write("\n ERROR: " . $e->getMessage() . ", row deleted \n");
            } catch (\Throwable $e) {
                $this->entityManager->getConnection()->rollBack();
                $output->write("\n ERROR: " . $e->getMessage() . "\n");
                throw new ImportStageException($e->getMessage());
            }
        }

        $this->entityManager->flush();
        $this->entityManager->getConnection()->commit();

        return $mainPhoto;
    }

}
