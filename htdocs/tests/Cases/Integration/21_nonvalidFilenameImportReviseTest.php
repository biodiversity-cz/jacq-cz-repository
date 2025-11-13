<?php

namespace Tests\Cases\Integration;


use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;

require __DIR__ . '/../../bootstrap.integration.php';

final class NonValidImportFilenameReviseTest extends IntegrationTestCase
{
    public const array SPECIMENS = ['7', '8', '9'];
    public const string DIR = 'nonvalidFilename';

    public function testRegisterNewFiles(): void
    {
        $this->checkBefore();
        $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(true)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(), ['photoType' => 1]);
        $this->expectAllError();

        $i=0;
        foreach ($this->em->getRepository(Photos::class)->findBy(['status' => PhotosStatus::IMAGE_CONTROL_ERROR]) as $photo) {
            $this->curatorFacade->reimportPhoto($this->provideLoggedCuratorUser(), $photo,  $this::SPECIMENS[$i++]);
            $this->em->flush();
        }

        $this->expectAllImported();
    }

}

new NonValidImportFilenameReviseTest()->run();
