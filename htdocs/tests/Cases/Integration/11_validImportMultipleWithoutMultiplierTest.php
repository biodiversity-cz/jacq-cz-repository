<?php

namespace Tests\Cases\Integration;


use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.integration.php';

final class ValidImportMultipleWithoutMultiplierTest extends IntegrationTestCase
{
    public const array SPECIMENS = ['20', '22'];
    public const string DIR = 'validMultiplier';

    public function testRegisterNewFiles(): void
    {
        $this->checkBefore();

        $this->em->getRepository(Herbaria::class)->find($this->user->getIdentity()->lastVisitedHerbarium)
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->user, ['photoType' => 3]);

        $this->expectAllError();

        foreach ($this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::CONTROL_ERROR]) as $photo) {
            $this->curatorFacade->deletePhoto($this->user, $photo);
            $this->em->flush();
        }
        $waitingAfterImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::CONTROL_ERROR]);
        Assert::count(0, $waitingAfterImport, 'images with error');

        $filesInCuratorBucket = $this->s3Service->listObjectsNamesOnly(self::BUCKET);
        Assert::count(0, $filesInCuratorBucket, 'curator bucket is empty again');
    }

}

new ValidImportMultipleWithoutMultiplierTest()->run();
