<?php declare(strict_types=1);

namespace App\Console\Scheduled;

use App\Model\Database\Entity\Photos;
use App\Services\Solr\SolrClientService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexSolr extends Command
{

    private const DB_BATCH_SIZE = 100;
    private const SOLR_BATCH_SIZE = 100;


    /**
     * Running as a CronJob - index all published photos data from CETAF databot to Solr, replace document logic (no partial updates)
     */
    public function __construct(protected readonly EntityManagerInterface $entityManager, protected readonly SolrClientService $solrClientService, ?string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('curator:reindexSolr');
        $this->setDescription(sprintf('index all published photos data from CETAF databot to Solr'));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        $lastId = 0;
        $buffer = [];

        do {
            $photos = $this->entityManager->getRepository(Photos::class)->getAllPublishedPhotosDatasource()
                ->andWhere('p.id > :lastId')
                ->setParameter('lastId', $lastId)
                ->orderBy('p.id', 'ASC')
                ->setMaxResults(self::DB_BATCH_SIZE)
                ->getQuery()
                ->getResult();

            foreach ($photos as $photo) {
                $buffer[] = $photo;
                $lastId = $photo->id;

                if (count($buffer) >= self::SOLR_BATCH_SIZE) {
                    $this->solrClientService->flushPhotos($buffer);
                    $buffer = [];
                }
            }

            $this->entityManager->clear();

        } while (count($photos) === self::DB_BATCH_SIZE);

        // flush zbytku
        $this->solrClientService->flushPhotos($buffer, true);
        $this->solrClientService->buildSuggest();
        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));

        return Command::SUCCESS;
    }

}
