<?php

declare(strict_types=1);

namespace App\Console\Scheduled;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\PhotosStatus;
use App\Services\SpecimenPidCallerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResolveSpecimenPid extends Command
{
    public const int LIMIT = 4;

    /**
     * Running as a CronJob - process images from curatorBucket to the repository waiting room, cleans expired Embargo.
     */
    public function __construct(protected readonly EntityManagerInterface $entityManager, protected readonly SpecimenPidCallerService $pidCallerService, protected readonly CuratorFacade $curatorFacade, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('curator:resolveSpecimenPid');
        $this->setDescription(sprintf('check if specimen PID exists and updates photo status to SPECIMEN_CONTROL_OK, cleans expired Embargo'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->curatorFacade->expireEmbargo();

        $startTime = microtime(true);
        try {
            $this->pidCallerService->callAsync($this->getPhotos(), 3);
        } catch (\Throwable $e) {
            $output->writeln("\n".$e->getMessage());

            return Command::FAILURE;
        }
        $output->writeln(sprintf("\n Execution time: %.2f sec", microtime(true) - $startTime));

        return Command::SUCCESS;
    }

    protected function getPhotos(): array
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id = ?  AND lastedit_timestamp < NOW() - INTERVAL \'6 hours\' ORDER BY lastedit_timestamp ASC FOR UPDATE SKIP LOCKED LIMIT 100 ', $rsm);
        $query->setParameter(1, PhotosStatus::IMAGE_CONTROL_OK);

        return $query->getResult();
    }
}
