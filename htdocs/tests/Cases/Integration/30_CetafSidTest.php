<?php

namespace Tests\Cases\Integration;


use App\Forms\BaseForm;
use App\Model\Database\Entity\PhotosStatus;
use App\Services\CetafSidManagementService;
use App\Services\EntityServices\CetafSidService;
use App\Services\EntityServices\PhotoService;
use Nette\Http\FileUpload;
use Nette\Http\Session;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.integration.php';

final class CetafSidTest extends IntegrationTestCase
{

    public const string DIR = 'nonvalid';

    protected BaseForm $form;

    public function testRegisterNewSids(): void
    {
        $values = new \stdClass();
        $values->table = new FileUpload(__DIR__ . '/files/' . 'cetaf_sid.xlsx');

        $service =  $this->container->getByType(CetafSidService::class);
        $servicePhotos =  $this->container->getByType(PhotoService::class);
        $session =  $this->container->getByType(Session::class);
        $managementService = new CetafSidManagementService($this->provideLoggedCuratorUser(), $session, $this->em, $service);
        $managementService->import($values);
        Assert::count(3, $service->findAll(), 'imported CETAF sids');

        $this->runCommand(['command' => 'curator:resolveSpecimenPid', '--no-interaction' => true,], 'sid resolve broken');

        Assert::count(3, $servicePhotos->findBy(['status'=>PhotosStatus::SPECIMEN_CONTROL_OK]), 'imported CETAF sids');

    }

}

new CetafSidTest()->run();
