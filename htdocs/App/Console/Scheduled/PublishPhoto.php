<?php

declare(strict_types=1);

namespace App\Console\Scheduled;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\ImportStages\Exceptions\ImportStageException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PublishPhoto extends Command
{
    public const int LIMIT = 40;

    /**
     * Continuously publishes photos from the repository waiting room.
     * Sleeps when there are no photos to process and exits after processing LIMIT photos.
     */
    public function __construct(protected readonly EntityManagerInterface $entityManager, protected readonly CuratorFacade $curatorFacade, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('curator:publishPhoto');
        $this->setDescription(sprintf('create JP2 for photo and mark it as published'));
        $this->addOption(
            'once',
            null,
            InputOption::VALUE_NONE,
            'Process available and exit' // used in integration tests
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $processed = 0;
        $once = $input->getOption('once');

        while ($processed < self::LIMIT) {
            try {
                $photo = $this->proceedPhoto($output);

                if (!$photo) {
                    if ($once) {
                        break;
                    }

                    $output->writeln('Nothing to process, sleeping 30 seconds...');
                    sleep(30);
                    continue;
                }
                ++$processed;
            } catch (ImportStageException $e) {
                $output->writeln("\n".$e->getMessage());
                $output->writeln(sprintf(
                    "\nProcessed: %d images\nExecution time: %.2f sec",
                    $processed,
                    microtime(true) - $startTime
                ));

                return Command::FAILURE;
            }
            if (memory_get_usage(true) > 2024 * 1024 * 1024) {
                $output->writeln("\nMemory limit reached.");

                break;
            }
        }

        $output->writeln(sprintf(
            "\nProcessed: %d images\nExecution time: %.2f sec",
            $processed,
            microtime(true) - $startTime
        ));

        return Command::SUCCESS;
    }

    protected function proceedPhoto(OutputInterface $output): ?Photos
    {
        $this->entityManager->getConnection()->beginTransaction(); // we are locking the selected row
        $photo = $this->getPhoto();
        if (null === $photo) {
            $this->entityManager->getConnection()->rollBack();

            return null;
        }

        try {
            $photo
                ->setStatus($this->entityManager->getReference(PhotosStatus::class, PhotosStatus::PUBLISHED))
                ->setLastEditAt();
            $this->curatorFacade->publishPhotoPipeline()->process($photo);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();

            return $photo;
        } catch (\Throwable $e) {
            $this->entityManager->getConnection()->rollBack();
            $output->write("\n ERROR: ".$e->getMessage()."\n");
            throw new ImportStageException($e->getMessage());
        }
    }

    protected function getPhoto(): ?Photos
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id = ? ORDER BY lastedit_timestamp asc FOR UPDATE SKIP LOCKED LIMIT 1 ', $rsm);
        $query->setParameter(1, PhotosStatus::WAITING_FOR_PUBLISHING);
        try {
            /** @var Photos $photo */
            $photo = $query->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }

        return $photo;
    }
}
