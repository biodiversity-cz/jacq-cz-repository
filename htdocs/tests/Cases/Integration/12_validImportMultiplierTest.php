<?php

namespace Tests\Cases\Integration;

use App\Console\Scheduled\ProceedCuratorImage;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use Tester\Assert;

require __DIR__.'/../../bootstrap.integration.php';

final class ValidImportMultiplierTest extends IntegrationTestCase
{
    public const array SPECIMENS = ['20', '21', '22', '23', '25'];
    public const string DIR = 'validMultiplier';

    public function testRegisterNewFiles(): void
    {
        $this->checkBefore();

        $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(true);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(), ['photoType' => 4]);

        $this->expectAllImported();
    }

    protected function checkBefore()
    {
        $filesInCuratorBucket = $this->s3Service->listObjectsNamesOnly(self::BUCKET_HERBARIUM);
        Assert::count(0, $filesInCuratorBucket, 'empty bucket at the beginning');

        $this->uploadFiles();

        $filesInCuratorBucket = $this->s3Service->listObjectsNamesOnly(self::BUCKET_HERBARIUM);
        Assert::count(2, $filesInCuratorBucket, 'files uploaded by curator in his bucket');

        $waiting = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::WAITING]);
        Assert::count(0, $waiting, 'no other images are waiting for processing');
        $importedBeforeImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::IMAGE_CONTROL_OK, 'specimenId' => $this::SPECIMENS]);
        Assert::count(0, $importedBeforeImport, 'these specimens do not exists in the db');
    }

    protected function expectAllImported(): void
    {
        $waiting = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::WAITING]);
        Assert::count(2, $waiting, 'al images marked as waiting');

        $rounds = ceil(count($this::SPECIMENS) / ProceedCuratorImage::LIMIT);
        for ($i = 0; $i < $rounds; ++$i) {
            $this->runCommand(['command' => 'curator:importImage', '--no-interaction' => true], 'import images failed');
        }

        $waitingAfterImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::WAITING, 'specimenId' => $this::SPECIMENS]);
        Assert::count(0, $waitingAfterImport, 'images processed, no more waiting');
        $importedAfterImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::IMAGE_CONTROL_OK, 'specimenId' => $this::SPECIMENS]);
        Assert::count(count($this::SPECIMENS), $importedAfterImport, 'exact ids imported');

        $filesInCuratorBucket = $this->s3Service->listObjectsNamesOnly(self::BUCKET_HERBARIUM);
        Assert::count(0, $filesInCuratorBucket, 'curator bucket is empty again');
    }
}

new ValidImportMultiplierTest()->run();
