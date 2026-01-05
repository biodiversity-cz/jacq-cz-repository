<?php

namespace Tests\Cases\Integration;


use App\Facades\CuratorFacade;
use App\Model\Database\Entity\PhotosStatus;
use App\Services\EntityServices\PhotoService;
use Tester\Assert;

require __DIR__ . '/../../bootstrap.integration.php';

final class PublishTest extends IntegrationTestCase
{

    public function testEmbargo(): void
    {
        $facade = $this->container->getByType(CuratorFacade::class);
        $servicePhotos = $this->container->getByType(PhotoService::class);
        $photos = $servicePhotos->findBy(['status' => PhotosStatus::SPECIMEN_CONTROL_OK]);

        Assert::count(4, $photos, 'CETAF OK original count');
        foreach ($photos as $item) {
            $facade->addEmbargoPhoto($this->provideLoggedCuratorUser(), $item);
        }
        $_ = $servicePhotos->findBy(['status' => PhotosStatus::SPECIMEN_CONTROL_OK]);
        Assert::count(0, $_, 'CETAF OK result count');

        $_ = $servicePhotos->findBy(['status' => PhotosStatus::EMBARGO]);
        Assert::count(4, $_, 'Embargo count');

        $photosForPublication = $servicePhotos->findBy(['status' => PhotosStatus::EMBARGO], null, 2);

        foreach ($photosForPublication as $item) {
            $facade->dropEmbargoPhoto($this->provideLoggedCuratorUser(), $item);
        }

        $_ = $servicePhotos->findBy(['status' => PhotosStatus::SPECIMEN_CONTROL_OK]);
        Assert::count(2, $_, 'CETAF OK final count');

        $_ = $servicePhotos->findBy(['status' => PhotosStatus::EMBARGO]);
        Assert::count(2, $_, 'Embargo final count');


    }

    public function testPublish(): void
    {
        $facade = $this->container->getByType(CuratorFacade::class);
        $servicePhotos = $this->container->getByType(PhotoService::class);

        $facade->markPublishable($this->provideLoggedCuratorUser());
        $_ = $servicePhotos->findBy(['status' => PhotosStatus::WAITING_FOR_PUBLISHING]);
        Assert::count(2, $_, 'CETAF OK count');

        $photosForPublication = $servicePhotos->findBy(['status' => PhotosStatus::EMBARGO], null, 1);
        foreach ($photosForPublication as $item) {
            $facade->dropEmbargoPhoto($this->provideLoggedCuratorUser(), $item);
        }

        $_ = $servicePhotos->findBy(['status' => PhotosStatus::EMBARGO]);
        Assert::count(1, $_, 'Embargo count');
    }

}

new PublishTest()->run();
