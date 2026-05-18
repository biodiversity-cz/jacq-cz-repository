<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Bootstrap;
use App\Model\Database\Entity\Databot;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Repository\DatabotRepository;
use App\Model\IIIF\ManifestFactory;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Nette\Application\LinkGenerator;
use Tester\Assert;
use Tests\Toolkit\PhotoTestFactory;
use Tests\Toolkit\SpecimenTestFactory;

require_once __DIR__.'/../../../../bootstrap.php';

test('ManifestFactory creates manifest with thumbnail and sequence', function (): void {
    $photo = PhotoTestFactory::detailed();
    $specimen = SpecimenTestFactory::minimal();

    $photoService = \Mockery::mock(PhotoService::class);
    $photoService->shouldReceive('getPublicPhotosOfSpecimen')->with($specimen)->andReturn([$photo]);

    $container = Bootstrap::boot()->createContainer();
    $repoConfig = $container->getByType(RepositoryConfiguration::class);

    $linkGenerator = $container->getByType(LinkGenerator::class);

    $repo = \Mockery::mock(EntityRepository::class);
    $repo->shouldReceive('getByName')
        ->with(DatabotRepository::HESPI_SHEET)
        ->andReturn(null);

    $em = \Mockery::mock(EntityManagerInterface::class);
    $em->shouldReceive('getRepository')
        ->with(Photos::class)
        ->andReturn($repo);
    $em->shouldReceive('getRepository')
        ->with(Databot::class)
        ->andReturn($repo);

    $factory = new ManifestFactory($em, $repoConfig, $linkGenerator, $photoService);

    $manifest = $factory->createManifest($specimen, 'http://manifest/1');

    Assert::equal('http://manifest/1', $manifest->getId());
    Assert::true(count($manifest->getThumbnails()) > 0);
    Assert::true(count($manifest->getSequences()) > 0);

    $sequence = $manifest->getSequences()[0];
    Assert::equal('http://manifest/1#sequence-1', $sequence->getId());
    Assert::true(1 === count($sequence->getCanvases()));

    $canvas = $sequence->getCanvases()[0];

    Assert::equal('test.jp2', $canvas->getLabels()[0]);
    Assert::equal(3000, $canvas->getWidth());
    Assert::equal(2000, $canvas->getHeight());
});

test('ManifestFactory omits logo when herbarium has no logo', function (): void {
    $photo = PhotoTestFactory::detailed();
    $specimen = SpecimenTestFactory::minimal();

    $photoService = \Mockery::mock(PhotoService::class);
    $photoService->shouldReceive('getPublicPhotosOfSpecimen')->with($specimen)->andReturn([$photo]);

    $container = Bootstrap::boot()->createContainer();
    $repoConfig = $container->getByType(RepositoryConfiguration::class);
    $linkGenerator = $container->getByType(LinkGenerator::class);

    $repo = \Mockery::mock(EntityRepository::class);
    $repo->shouldReceive('getByName')
        ->with(DatabotRepository::HESPI_SHEET)
        ->andReturn(null);

    $em = \Mockery::mock(EntityManagerInterface::class);
    $em->shouldReceive('getRepository')
        ->with(Photos::class)
        ->andReturn($repo);
    $em->shouldReceive('getRepository')
        ->with(Databot::class)
        ->andReturn($repo);

    $factory = new ManifestFactory($em, $repoConfig, $linkGenerator, $photoService);

    $manifest = $factory->createManifest($specimen, 'http://manifest/2');

    Assert::equal('http://manifest/2', $manifest->getId());
    Assert::equal([], $manifest->getLogos()); // žádné logo
});

test('ManifestFactory::getFirstImage returns null when there are no public photos', function (): void {
    $specimen = SpecimenTestFactory::minimal();

    // PhotoService vrátí prázdné pole
    $photoService = \Mockery::mock(PhotoService::class);
    $photoService->shouldReceive('getPublicPhotosOfSpecimen')
        ->with($specimen)
        ->andReturn([]);

    // DI služby z containeru (stejně jako v ostatních testech)
    $container = Bootstrap::boot()->createContainer();
    $repoConfig = $container->getByType(RepositoryConfiguration::class);
    $linkGenerator = $container->getByType(LinkGenerator::class);

    // EntityManager musí vrátit EntityRepository (typový hint v konstruktoru)
    $repo = \Mockery::mock(EntityRepository::class);
    $repo->shouldReceive('getByName')
        ->with(DatabotRepository::HESPI_SHEET)
        ->andReturn(null);

    $em = \Mockery::mock(EntityManagerInterface::class);
    $em->shouldReceive('getRepository')
        ->with(Photos::class)
        ->andReturn($repo);
    $em->shouldReceive('getRepository')
        ->with(Databot::class)
        ->andReturn($repo);

    $factory = new ManifestFactory($em, $repoConfig, $linkGenerator, $photoService);

    // Nastavíme chráněnou property $specimen, protože getFirstImage() ji používá
    $rp = new \ReflectionProperty(ManifestFactory::class, 'specimen');

    $rp->setValue($factory, $specimen);

    // Zavoláme protected metodu getFirstImage() přes Reflection
    $rm = new \ReflectionMethod(ManifestFactory::class, 'getFirstImage');

    $result = $rm->invoke($factory);

    Assert::null($result);
});
