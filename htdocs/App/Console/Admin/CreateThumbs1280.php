<?php declare(strict_types = 1);

namespace App\Console\Admin;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\Services\TempDir;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateThumbs1280 extends Command
{

    protected const string TEMPNAME = DIRECTORY_SEPARATOR . 'thumb640.tif';
    protected const string TEMPNAME2 = DIRECTORY_SEPARATOR . 'thumb640.png';

    public function __construct(protected readonly EntityManagerInterface $entityManager, protected readonly CuratorFacade $curatorService, protected readonly TempDir $tempDir, protected readonly ImagickService $imageService, protected RepositoryConfiguration $repositoryConfiguration, protected S3Service $s3Service, ?string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * @return Photos[]
     */
    public function getListOfPhotos(): ?array
    {
        $rsm = new ResultSetMappingBuilder($this->entityManager);
        $rsm->addRootEntityFromClassMetadata('App\Model\Database\Entity\Photos', 'p');
        $query = $this->entityManager->createNativeQuery('SELECT p.* FROM photos p WHERE status_id IN (?) ORDER BY id asc', $rsm);
        $query->setParameter(1, PhotosStatus::PASSED);

        return $query->execute();
    }

    protected function configure(): void
    {
        $this->setName('admin:generate640Thumbs');
        $this->setDescription('generate new png files with low resolution used for AI etc.');
    }

    protected function tempFile(): string
    {
        return $this->tempDir->getPath(self::TEMPNAME);
    }

    protected function tempFile2(): string
    {
        return $this->tempDir->getPath(self::TEMPNAME2);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);
        foreach ($this->getListOfPhotos() as $photo) {
            if ($this->s3Service->objectExists($this->repositoryConfiguration->getRepositoryDatabotThumbsBucket(), $this->repositoryConfiguration->createS3DatabotThumbName($photo)))
                continue;

            try {
                $output->writeln("Processing photoId: {$photo->getId()}");
                $this->curatorService->getArchiveFile($photo, $this->tempFile());
                $imagick = $this->imageService->createImagick($this->tempFile());
                $imagick = $this->imageService->preparePngThumb($imagick, 1280);
                $imagick->writeImage($this->tempFile2());
                $imagick->clear();
                unlink($this->tempFile());
                $this->s3Service->putPngIfNotExists($this->repositoryConfiguration->getRepositoryDatabotThumbsBucket(), $this->repositoryConfiguration->createS3DatabotThumbName($photo), $this->tempFile2());
                unlink($this->tempFile2());
            } catch (\ImagickException $e) {
                $output->writeln("Error with ID {$photo->getId()} -->  ".$e->getMessage());
                $output->writeln("Check the original file manually via: rclone copy repository_jacq:archive/{$photo->getArchiveFilename()} . --progress");
            } finally {
                unlink($this->tempFile());
                unlink($this->tempFile2());
            }

        }

        $output->writeln(sprintf("\n Execution time: %.2f sec", (microtime(true) - $startTime)));

        return Command::SUCCESS;
    }

}
