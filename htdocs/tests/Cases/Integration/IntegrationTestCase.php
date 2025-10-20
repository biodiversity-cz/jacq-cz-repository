<?php

namespace Tests\Cases\Integration;

use App\Console\Scheduled\ProceedCuratorImage;
use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Security\Identity;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use Contributte\Console\Application;
use Doctrine\ORM\EntityManagerInterface;
use Nette\DI\Container;
use Nette\Security\User;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tester\Assert;
use Tester\TestCase;

abstract class IntegrationTestCase extends TestCase
{
    protected const string BUCKET = 'herbarium-test';
    public const string DIR = '';
    protected EntityManagerInterface $em;
    protected Container $container;
    protected S3Service $s3Service;
    protected RepositoryConfiguration $repositoryConfiguration;
    protected ?User $user;
    protected CuratorFacade $curatorFacade;
    protected Application $application;

    protected function setUp(): void
    {
        $this->container = $GLOBALS['container'];

        $this->em = $this->container->getByType(EntityManagerInterface::class);
        $this->s3Service = $this->container->getByType(S3Service::class);
        $this->repositoryConfiguration = $this->container->getByType(RepositoryConfiguration::class);
        $this->curatorFacade = $this->container->getByType(CuratorFacade::class);

        $this->application = $this->container->getByType(Application::class);
        $this->application->setAutoExit(false);

    }

    protected function provideLoggedCuratorUser(): User
    {
        if (empty($this->user)) {
            $identity = new Identity($this->getUserEntity(3));
            $this->user = $this->container->getByType(User::class);
            $this->user->login($identity);
        }
        return $this->user;
    }

    protected function getUserEntity(int $id): \App\Model\Database\Entity\User
    {
        return $this->em->getRepository(\App\Model\Database\Entity\User::class)->find($id);
    }

    protected function checkBefore()
    {
        $filesInCuratorBucket = $this->s3Service->listObjectsNamesOnly(self::BUCKET);
        Assert::count(0, $filesInCuratorBucket, 'empty bucket at the beginning');

        $this->uploadFiles();

        $filesInCuratorBucket = $this->s3Service->listObjectsNamesOnly(self::BUCKET);
        Assert::count(count($this::SPECIMENS), $filesInCuratorBucket, 'files uploaded by curator in his bucket');

        $waiting = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::WAITING]);
        Assert::count(0, $waiting, 'no other images are waiting for processing');
        $importedBeforeImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::IMPORTED, 'specimenId' => $this::SPECIMENS]);
        Assert::count(0, $importedBeforeImport, 'these specimens already exists in the db');
    }

    protected function uploadFiles(): void
    {

        $sampleDir = __DIR__ . '/files/' . $this::DIR;
        $tifFiles = glob($sampleDir . '/*.tif');

        foreach ($tifFiles as $path) {
            $key = basename($path);
            if (!$this->s3Service->objectExists(self::BUCKET, $key)) {
                $this->s3Service->putTiffIfNotExists(self::BUCKET, $key, $path);
            }
        }

    }

    protected function expectAllImported(): void
    {
        $waiting = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::WAITING]);
        Assert::count(count($this::SPECIMENS), $waiting, 'al images marked as waiting');

        $rounds = ceil(count($this::SPECIMENS)/ProceedCuratorImage::LIMIT);
        for ($i = 0; $i < $rounds; $i++) {
            $this->runCommand(['command' => 'curator:importImage', '--no-interaction' => true,], 'import images failed');
        }

        $waitingAfterImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::WAITING, 'specimenId' => $this::SPECIMENS]);
        Assert::count(0, $waitingAfterImport, 'images processed, no more waiting');
        $importedAfterImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::IMPORTED, 'specimenId' => $this::SPECIMENS]);
        Assert::count(count($this::SPECIMENS), $importedAfterImport, 'exact ids imported');

        $filesInCuratorBucket = $this->s3Service->listObjectsNamesOnly(self::BUCKET);
        Assert::count(0, $filesInCuratorBucket, 'curator bucket is empty again');
    }

    protected function expectAllError(): void
    {
        $waiting = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::WAITING]);
        Assert::count(count($this::SPECIMENS), $waiting, 'al images marked as waiting');

        $rounds = ceil(count($this::SPECIMENS)/ProceedCuratorImage::LIMIT);
        for ($i = 0; $i < $rounds; $i++) {
            $this->runCommand(['command' => 'curator:importImage', '--no-interaction' => true,], 'import images failed');
        }

        $waitingAfterImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::CONTROL_ERROR]);
        Assert::count(count($this::SPECIMENS), $waitingAfterImport, 'images with error');

        $importedAfterImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::IMPORTED, 'specimenId' => $this::SPECIMENS]);
        Assert::count(0, $importedAfterImport, 'exact ids imported');

        $filesInCuratorBucket = $this->s3Service->listObjectsNamesOnly(self::BUCKET);
        Assert::count(count($this::SPECIMENS), $filesInCuratorBucket, 'curator bucket contains all the errorneus');
    }

    protected function runCommand(array $args, string $errorMessage = ''): void
    {
        $input = new ArrayInput($args);
        $output = new BufferedOutput();
        $exitCode = $this->application->run($input, $output);
        if ($exitCode !== 0) {
            throw new RuntimeException($errorMessage . "\n" . $output->fetch());
        }
    }

}
