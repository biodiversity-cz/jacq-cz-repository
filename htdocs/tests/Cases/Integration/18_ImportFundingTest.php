<?php

namespace Tests\Cases\Integration;


use App\Model\Database\Entity\Herbaria;

require __DIR__ . '/../../bootstrap.integration.php';

final class ImportFundingTest extends IntegrationTestCase
{
    public const array SPECIMENS = ['10', '11'];
    public const string DIR = 'funding';

    public function testNonactiveFunding(): void
    {
        $this->checkBefore();

        $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(),
            [
                'photoType' => 1,
                'funding' => 2 //non-active
            ]);
         $this->expectAllError();
    }

    public function testForeignFunding(): void
    {
        $this->checkBefore();

        $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(),
            [
                'photoType' => 1,
                'funding' => 3 //PRC funding
            ]);
        $this->expectAllError();
    }

    public function testCorrectFunding(): void
    {
        $this->checkBefore();

        $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(),
            [
                'photoType' => 1,
                'funding' => 1 //general funding
            ]);
        $this->expectAllImported();
    }

}

new ImportFundingTest()->run();
