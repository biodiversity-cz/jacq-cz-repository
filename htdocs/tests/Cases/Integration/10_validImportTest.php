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
//        //         https://phpfashion.com/cs/velestrucne-testovani-presenteru-v-nette
////        https://github.com/webnazakazku/mango-presenter-tester
////        $presenterFactory = $this->container->getByType(IPresenterFactory::class);
////        $presenter = $presenterFactory->createPresenter('Admin:Import');
////        $presenter->autoCanonicalize = false;
////        $post = [
////
////            'importForm' => [          // jméno komponenty
////                'photoType' => PhotosStatus::WAITING,
////                'send' => 'Send',      // jméno submit buttonu
////            ]
////        ];
////
////        $request = new Request('Admin:Import', 'POST', array('action' => 'default'), $post);
////
////        $response = $presenter->run($request);


        $this->expectAllImported();
    }

}

new ValidImportTest()->run();
