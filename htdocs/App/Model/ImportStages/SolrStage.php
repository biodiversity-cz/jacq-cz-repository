<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Databot;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Repository\DatabotRepository;
use App\Model\ImportStages\Exceptions\PublishStageException;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\Solr\SolrClientService;
use App\Services\TempDir;
use Doctrine\ORM\EntityManagerInterface;
use League\Pipeline\StageInterface;

class SolrStage extends BaseStage implements StageInterface
{
    public function __construct(
        TempDir $tempDir,
        RepositoryConfiguration $repositoryConfiguration,
        ImagickService $imagickService,
        private readonly SolrClientService $solrClientService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($tempDir, $repositoryConfiguration, $imagickService);
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        try {
            /* @var Photos $payload */
            $cetafDatabot = $this->entityManager->getRepository(Databot::class)->getByName(DatabotRepository::CETAF);

            $data = [
                'pid' => $payload->pid,
                'acronym' => $payload->herbarium->acronym,
                'resultData' => $cetafDatabot ? $payload->getDatabotOkResultById($cetafDatabot)->resultData : null,
            ];
            $this->solrClientService->flushPhotos([$data], true);
        } catch (\Throwable $exception) {
            throw new PublishStageException('unable index specimen in Solr: '.$exception->getMessage().' '.$payload->id);
        }

        return $payload;
    }
}
