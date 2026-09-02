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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResolveSpecimenPid extends Command
{
    public const int LIMIT = 400;
    protected bool $stopping = false;

    /**
     * check if a specimen PID exists in an external database and updates photo status to SPECIMEN_CONTROL_OK, cleans expired Embargo.
     */
    public function __construct(protected readonly EntityManagerInterface $entityManager, protected readonly SpecimenPidCallerService $pidCallerService, protected readonly CuratorFacade $curatorFacade, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('curator:resolveSpecimenPid');
        $this->setDescription(sprintf('check if specimen PID exists and updates photo status to SPECIMEN_CONTROL_OK, cleans expired Embargo'));
        $this->addOption(
            'once',
            null,
            InputOption::VALUE_NONE,
            'Process available and exit' // used in integration tests
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //TODO
//        pcntl_async_signals(true);
//
//        pcntl_signal(SIGTERM, function () {
//            $this->stopping = true;
//        });

        $startTime = microtime(true);
        $cycles = 0;
        $once = $input->getOption('once');

        while (!$this->stopping && $cycles < self::LIMIT) {
            ++$cycles;
            try {
                $this->curatorFacade->expireEmbargo();
                $this->pidCallerService->callAsync($this->getPhotos(), 3);
                if ($once) {
                    break;
                }
            } catch (\Throwable $e) {
                $output->writeln("\n".$e->getMessage());

                return Command::FAILURE;
            }

            if (memory_get_usage(true) > 150 * 1024 * 1024) {
                $output->writeln("\nMemory limit reached.");

                break;
            }

            sleep(30);
        }

        $output->writeln(sprintf(
            "\nProcessed: %d cycles\nExecution time: %.2f sec",
            $cycles,
            microtime(true) - $startTime
        ));

        return Command::SUCCESS;
    }

    protected function getPhotos(): array
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('
            SELECT p.* FROM photos p
           WHERE status_id = ?
             AND (
                lastedit_timestamp < NOW() - INTERVAL \'6 hours\'
                OR
                lastedit_timestamp - created_at < INTERVAL \'6 hours\'
                )
           ORDER BY lastedit_timestamp ASC
           FOR UPDATE SKIP LOCKED LIMIT 100 ', $rsm);
        $query->setParameter(1, PhotosStatus::IMAGE_CONTROL_OK);

        return $query->getResult();
    }
}
