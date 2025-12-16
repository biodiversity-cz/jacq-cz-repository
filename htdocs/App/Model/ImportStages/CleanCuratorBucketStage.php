<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\CleanupStageException;
use App\Services\AppConfiguration;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use League\Pipeline\StageInterface;

class CleanCuratorBucketStage extends BaseStage implements StageInterface
{

    public function __construct(TempDir $tempDir, RepositoryConfiguration $repositoryConfiguration, ImagickService $imagickService, protected S3Service $s3Service, protected AppConfiguration $appConfiguration)
    {
        parent::__construct($tempDir, $repositoryConfiguration, $imagickService);
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        $this->deleteTifFromCuratorBucket();

        return $payload;
    }

    protected function deleteTifFromCuratorBucket(): void
    {
        try {
            $this->s3Service->deleteObject($this->item->herbarium->bucket, $this->item->originalFilename);
        } catch (\Throwable $exception) {
            throw new CleanupStageException('deleting tif from curatorBucket error (' . $exception->getMessage() . ')');
        }
    }
}
