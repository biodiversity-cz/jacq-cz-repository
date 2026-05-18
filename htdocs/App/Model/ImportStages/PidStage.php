<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\PublishStageException;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\SpecimenIdService;
use App\Services\TempDir;
use League\Pipeline\StageInterface;

class PidStage extends BaseStage implements StageInterface
{
    public function __construct(TempDir $tempDir, RepositoryConfiguration $repositoryConfiguration, ImagickService $imagickService, protected readonly SpecimenIdService $specimenIdService)
    {
        parent::__construct($tempDir, $repositoryConfiguration, $imagickService);
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        try {
            /* @var Photos $payload */
            $payload->setPid($this->specimenIdService->generateArk($payload));
        } catch (\Throwable $exception) {
            throw new PublishStageException('unable assign ARK ('.$exception->getMessage().'): '.$payload->id);
        }

        return $payload;
    }
}
