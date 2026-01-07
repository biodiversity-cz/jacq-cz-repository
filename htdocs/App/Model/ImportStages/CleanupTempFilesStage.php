<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\TempDir;
use League\Pipeline\StageInterface;

class CleanupTempFilesStage extends BaseStage implements StageInterface
{

    public function __construct(TempDir $tempDir, RepositoryConfiguration $repositoryConfiguration, ImagickService $imagickService)
    {
        parent::__construct($tempDir, $repositoryConfiguration, $imagickService);
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        $files = [$this->getIiifTempPath(), $this->getMasterTempPath(), $this->getDatabotThumbTempPath(), $this->getZbarThumbTempPath(), $this->getMasterSinglePageTempPath()];

        foreach ($files as $path) {
            if ($path && file_exists($path)) {
                @unlink($path);
            }
        }
        return $payload;
    }

}
