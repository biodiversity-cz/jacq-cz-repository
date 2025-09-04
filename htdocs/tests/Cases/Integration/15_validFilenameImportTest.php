<?php

namespace Tests\Cases\Integration;


use App\Model\Database\Entity\Herbaria;

require __DIR__ . '/../../bootstrap.integration.php';

final class ValidImportFilenameTest extends IntegrationTestCase
{
    public const array SPECIMENS = ['5', '6'];
    public const string DIR = 'validFilename';

    public function testRegisterNewFiles(): void
    {

        $this->checkBefore();
//        $this->getUserEntity()->getHerbarium() //tohle nějak nepersistuje, musí se vzít rovnou ta entita
        $this->em->getRepository(Herbaria::class)->find($this->user->getIdentity()->herbarium)
            ->setFallbackFilename(true)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->user, ['photoType' => 1]);

        $this->expectAllImported();
    }

}

new ValidImportFilenameTest()->run();
