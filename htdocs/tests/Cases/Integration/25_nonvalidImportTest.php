<?php
namespace Tests\Cases\Integration;


use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.integration.php';

final class NonValidImportTest extends IntegrationTestCase
{
    public const array SPECIMENS = ['0001-012', 'prc-128', 'prc-0000128', 'test-a0001', 'test-a0001+PRC-128', 'empty'];
    public const string DIR = 'nonvalid';

    public function testRegisterNewFiles(): void
    {

        $this->checkBefore();
        $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(), ['photoType' => 2]);

        $this->expectAllError();

        foreach ($this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::IMAGE_CONTROL_ERROR]) as $photo) {
            $this->curatorFacade->deletePhoto($this->provideLoggedCuratorUser(), $photo);
            $this->em->flush();
        }
        $waitingAfterImport = $this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::IMAGE_CONTROL_ERROR]);
        Assert::count(0, $waitingAfterImport, 'images with error');

        $filesInCuratorBucket = $this->s3Service->listObjectsNamesOnly(self::BUCKET);
        Assert::count(0, $filesInCuratorBucket, 'curator bucket is empty again');
    }

}

new NonValidImportTest()->run();
