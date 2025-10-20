<?php

namespace Tests\Cases\Integration;


use App\Model\Database\Entity\Herbaria;
use Doctrine\ORM\NoResultException;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.integration.php';

final class ImportFundingTest extends IntegrationTestCase
{
    public const array SPECIMENS = ['15', '16'];
    public const string DIR = 'funding';

    public function test10NonactiveFunding(): void
    {
        $this->checkBefore();

        $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();

        Assert::exception(fn() => $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(),
            [
                'photoType' => 1,
                'funding' => 2 //non-active
            ]), NoResultException::class
        );

    }

    public function test20ForeignFunding(): void
    {
           $this->em->getRepository(Herbaria::class)->find($this->provideLoggedCuratorUser()->getIdentity()->getCurrentHerbariumId())
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false);
        $this->em->flush();
        Assert::exception(fn() =>
        $this->curatorFacade->registerNewFiles($this->provideLoggedCuratorUser(),
            [
                'photoType' => 1,
                'funding' => 3 //PRC funding
            ]), NoResultException::class
        );
    }

    public function test30CorrectFunding(): void
    {
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
