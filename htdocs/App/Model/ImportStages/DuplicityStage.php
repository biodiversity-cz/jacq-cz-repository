<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\DuplicityStageException;
use App\Services\EntityServices\PhotoService;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use League\Pipeline\StageInterface;
use Nette\Application\LinkGenerator;

class DuplicityStage extends BaseStage implements StageInterface
{

    public function __construct(TempDir $tempDir, RepositoryConfiguration $repositoryConfiguration, ImagickService $imagickService, protected readonly PhotoService $photoService, protected readonly LinkGenerator $linkGenerator, protected readonly S3Service $s3Service)
    {
        parent::__construct($tempDir, $repositoryConfiguration, $imagickService);
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        $duplicities = $this->photoService->findPotentialDuplicates($this->item);
        if (count($duplicities) > 0) {
            $imagickNewFile = $this->imagickService->createImagick($this->getMasterTempPath());
            foreach ($duplicities as $duplicate) {
                $this->s3Service->getObject($this->repositoryConfiguration->getRepositoryArchiveBucket(), $duplicate->getArchiveFilename(), $this->getDuplicateTempPath($duplicate));

                $imagickFromDuplicateCandidate = $this->imagickService->createImagick($this->getDuplicateTempPath($duplicate));
                if ($imagickNewFile->getImageSignature() === $imagickFromDuplicateCandidate->getImageSignature()) {
                    $this->item->getError()->setDuplicateTo($duplicate);

                    throw new DuplicityStageException('suspicious similarity with already imported file');
                }

                $imagickFromDuplicateCandidate->clear();
                unset($imagickFromDuplicateCandidate);
            }

            $imagickNewFile->clear();
            unset($imagickNewFile);
        }

        return $payload;
    }

}
