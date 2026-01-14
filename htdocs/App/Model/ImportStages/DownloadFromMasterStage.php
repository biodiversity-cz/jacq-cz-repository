<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\DownloadStageException;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use League\Pipeline\StageInterface;

class DownloadFromMasterStage extends BaseStage implements StageInterface
{

    public function __construct(TempDir $tempDir, RepositoryConfiguration $repositoryConfiguration, ImagickService $imagickService, protected S3Service $s3Service)
    {
        parent::__construct($tempDir, $repositoryConfiguration, $imagickService);
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;

        try {
            $this->s3Service->getObject($this->repositoryConfiguration->getArchiveBucket($this->item), $this->item->archiveFilename, $this->getMasterTempPath());

        } catch (\Throwable $exception) {
            throw new DownloadStageException('download master file error (' . $exception->getMessage() . ')');
        }

        return $this->item;
    }

}
