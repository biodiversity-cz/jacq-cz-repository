<?php

namespace Tests\Cases\Integration;


use App\Model\Database\Entity\Herbaria;

require __DIR__ . '/../../bootstrap.integration.php';

final class ValidImportTest extends IntegrationTestCase
{
    public const array SPECIMENS = ['1', '10', '11', '110'];
    public const string DIR = 'valid';

    public function testRegisterNewFiles(): void
    {
        $this->checkBefore();

        $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(), ['photoType' => 1]);
        $this->expectAllImported();
    }

}

new ValidImportTest()->run();
