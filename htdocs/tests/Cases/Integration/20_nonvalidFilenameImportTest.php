<?php
namespace Tests\Cases\Integration;


use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.integration.php';

final class NonValidImportFilenameTest extends IntegrationTestCase
{
    public const array SPECIMENS = ['7', '8', '9'];
    public const string DIR = 'nonvalidFilename';

    public function testRegisterNewFiles(): void
    {

        $this->checkBefore();
//        $this->getUserEntity()->herbarium //tohle nějak nepersistuje, musí se vzít rovnou ta entita
        $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(true)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(), ['photoType' => 1]);

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

new NonValidImportFilenameTest()->run();
