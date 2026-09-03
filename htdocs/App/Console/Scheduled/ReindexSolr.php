<?php

declare(strict_types=1);

namespace App\Console\Scheduled;

use App\Model\Database\Entity\Databot;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Repository\DatabotRepository;
use App\Services\Solr\SolrClientService;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReindexSolr extends Command
{
    private const DB_BATCH_SIZE = 500;
    private const SOLR_BATCH_SIZE = 500;

    /**
     * Running as a CronJob - index all published photos data from CETAF databot to Solr, replace document logic (no partial updates).
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

        $cetafDatabot = $this->entityManager
            ->getRepository(Databot::class)
            ->getByName(DatabotRepository::CETAF);

        do {
            $output->writeln(sprintf('ID: %s', $lastId));
            $query = $this->entityManager->getRepository(Photos::class)->getAllPublishedPhotosDatasource()
                // add databot results
                ->select('p.id, p.pid, h.acronym, r.resultData')
                ->leftJoin('p.databotResults', 'r', 'WITH', 'r.status = :statusOK AND r.databot = :databot')
                ->leftJoin('r.databot', 'd')
                ->leftJoin('p.herbarium', 'h')
                ->setParameter('statusOK', 'ok')
                ->setParameter('databot', $cetafDatabot)
                // batch
                ->andWhere('p.id > :lastId')
                ->setParameter('lastId', $lastId)
                ->orderBy('p.id', 'ASC')
                ->setMaxResults(self::DB_BATCH_SIZE)
                ->getQuery();

            $query->useQueryCache(false);
            $query->disableResultCache();

            $photos = $query->getResult(AbstractQuery::HYDRATE_ARRAY);
            $fetchedCount = count($photos);
            foreach ($photos as $photo) {
                $buffer[] = $photo;
                $lastId = $photo['id'];

                if (count($buffer) >= self::SOLR_BATCH_SIZE) {
                    $this->solrClientService->flushPhotos($buffer);
                    $buffer = [];
                }
            }
            unset($photos, $photo);
        } while ($fetchedCount > 0);

        // flush zbytku
        $this->solrClientService->flushPhotos($buffer, true);
        $this->solrClientService->buildSuggest();
        $output->writeln(sprintf("\n Execution time: %.2f sec", microtime(true) - $startTime));

        return Command::SUCCESS;
    }
}
