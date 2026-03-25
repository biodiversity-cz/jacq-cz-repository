<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\PublishStageException;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\TempDir;
use League\Pipeline\StageInterface;

class SolrStage extends BaseStage implements StageInterface
{
    public function __construct(TempDir $tempDir, RepositoryConfiguration $repositoryConfiguration, ImagickService $imagickService)
    {
        parent::__construct($tempDir, $repositoryConfiguration, $imagickService);
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        try {
            /** @var Photos $payload */

        } catch (\Throwable $exception) {
            throw new PublishStageException('unable index specimen in Solr: ' . $payload->id);
        }

        return $payload;
    }


}
