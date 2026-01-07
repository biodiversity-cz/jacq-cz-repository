<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\TransferStageException;
use App\Services\AppConfiguration;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use League\Pipeline\StageInterface;

class TransferJp2Stage extends BaseStage implements StageInterface
{

    public function __construct(TempDir $tempDir, RepositoryConfiguration $repositoryConfiguration, ImagickService $imagickService, protected readonly AppConfiguration $appConfiguration, protected readonly S3Service $s3Service)
    {
        parent::__construct($tempDir, $repositoryConfiguration, $imagickService);
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        $this->uploadJp2toRepository();
        return $payload;
    }

    protected function uploadJp2toRepository(): void
    {
        try {
            $this->s3Service->putJp2IfNotExists(
                $this->repositoryConfiguration->getImageServerBucket($this->item),
                $this->repositoryConfiguration->createS3Jp2Name($this->item),
                $this->getIiifTempPath());
            $this->item->setJP2Filename($this->repositoryConfiguration->createS3Jp2Name($this->item));
        } catch (\Throwable $exception) {
            throw new TransferStageException('jp2 upload error (' . $exception->getMessage() . ')');
        }
    }

}
