<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\DuplicityStageException;
use App\Services\EntityServices\PhotoService;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use League\Pipeline\StageInterface;
use Nette\Application\LinkGenerator;

class DuplicityStage implements StageInterface
{

    protected Photos $item;

    public function __construct(protected readonly PhotoService $photoService, protected readonly LinkGenerator $linkGenerator, protected readonly ImagickService $imageService, protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly S3Service $s3Service)
    {
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        $duplicities = $this->photoService->findPotentialDuplicates($this->item);
        if (count($duplicities) > 0) {
            $imagickNewFile = $this->imageService->createImagick($this->repositoryConfiguration->getImportTempPath($this->item));
            foreach ($duplicities as $duplicate) {
                $this->s3Service->getObject($this->repositoryConfiguration->getRepositoryArchiveBucket(), $duplicate->getArchiveFilename(), $this->repositoryConfiguration->getImportTempDuplicatePath($duplicate));

                $imagickFromDuplicateCandidate = $this->imageService->createImagick($this->repositoryConfiguration->getImportTempDuplicatePath($duplicate));
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
