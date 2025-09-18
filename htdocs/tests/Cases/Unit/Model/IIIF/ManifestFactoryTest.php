<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Bootstrap;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\License;
use App\Model\Database\Entity\Photos;
use App\Model\IIIF\ManifestFactory;
use App\Model\Specimen\Specimen;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\LinkGenerator;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';


test('ManifestFactory creates manifest with thumbnail and sequence', function (): void {
    $photo = \Mockery::mock(Photos::class);
    $photo->shouldReceive('getJp2Filename')->andReturn('image.jp2');
    $photo->shouldReceive('getId')->andReturn(123);
    $photo->shouldReceive('getWidth')->andReturn(800);
    $photo->shouldReceive('getHeight')->andReturn(600);

    $specimen = \Mockery::mock(Specimen::class);
    $herbarium = \Mockery::mock(Herbaria::class);
    $license = \Mockery::mock(License::class);
    $license->shouldReceive('getLink')->andReturn('http://license');
    $herbarium->shouldReceive('getLogo')->andReturn('http://logo.png');
    $herbarium->shouldReceive('getLicense')->andReturn($license);
    $specimen->shouldReceive('getHerbarium')->andReturn($herbarium);

    $photoService = \Mockery::mock(PhotoService::class);
    $photoService->shouldReceive('getPublicPhotosOfSpecimen')->with($specimen)->andReturn([$photo]);

    $container = Bootstrap::boot()->createContainer();
    $repoConfig = $container->getByType(RepositoryConfiguration::class);

    $linkGenerator = $container->getByType(LinkGenerator::class);

    $repo = \Mockery::mock(\Doctrine\ORM\EntityRepository::class);

    $em = \Mockery::mock(EntityManagerInterface::class);
    $em->shouldReceive('getRepository')
        ->with(Photos::class)
        ->andReturn($repo);

    $factory = new ManifestFactory($em, $repoConfig, $linkGenerator, $photoService);

    $manifest = $factory->createManifest($specimen, 'http://manifest/1');

    Assert::equal('http://manifest/1', $manifest->getId());
    Assert::true(count($manifest->getThumbnails()) > 0);
    Assert::true(count($manifest->getSequences()) > 0);

    $sequence = $manifest->getSequences()[0];
    Assert::equal('http://manifest/1#sequence-1', $sequence->getId());
    Assert::true(count($sequence->getCanvases()) === 1);

    $canvas = $sequence->getCanvases()[0];

    Assert::equal('image.jp2', $canvas->getLabels()[0]);
    Assert::equal(800, $canvas->getWidth());
    Assert::equal(600, $canvas->getHeight());
});

test('ManifestFactory omits logo when herbarium has no logo', function (): void {
    $photo = \Mockery::mock(Photos::class);
    $photo->shouldReceive('getJp2Filename')->andReturn('image.jp2');
    $photo->shouldReceive('getId')->andReturn(123);
    $photo->shouldReceive('getWidth')->andReturn(800);
    $photo->shouldReceive('getHeight')->andReturn(600);

    $herbarium = \Mockery::mock(Herbaria::class);
    $license = \Mockery::mock(License::class);
    $license->shouldReceive('getLink')->andReturn('http://license');
    $herbarium->shouldReceive('getLicense')->andReturn($license);
    $herbarium->shouldReceive('getLogo')->andReturn(null);

    $specimen = \Mockery::mock(Specimen::class);
    $specimen->shouldReceive('getHerbarium')->andReturn($herbarium);

    $photoService = \Mockery::mock(PhotoService::class);
    $photoService->shouldReceive('getPublicPhotosOfSpecimen')->with($specimen)->andReturn([$photo]);

    $container = Bootstrap::boot()->createContainer();
    $repoConfig = $container->getByType(RepositoryConfiguration::class);
    $linkGenerator = $container->getByType(LinkGenerator::class);

    $repo = \Mockery::mock(\Doctrine\ORM\EntityRepository::class);
    $em = \Mockery::mock(EntityManagerInterface::class);
    $em->shouldReceive('getRepository')
        ->with(Photos::class)
        ->andReturn($repo);

    $factory = new ManifestFactory($em, $repoConfig, $linkGenerator, $photoService);

    $manifest = $factory->createManifest($specimen, 'http://manifest/2');

    Assert::equal('http://manifest/2', $manifest->getId());
    Assert::equal([], $manifest->getLogos()); // žádné logo
});

test('ManifestFactory::getFirstImage returns null when there are no public photos', function (): void {
    // Specimen, pro který nic není
    $specimen = \Mockery::mock(\App\Model\Specimen\Specimen::class);

    // PhotoService vrátí prázdné pole
    $photoService = \Mockery::mock(\App\Services\EntityServices\PhotoService::class);
    $photoService->shouldReceive('getPublicPhotosOfSpecimen')
        ->with($specimen)
        ->andReturn([]);

    // DI služby z containeru (stejně jako v ostatních testech)
    $container = \App\Bootstrap::boot()->createContainer();
    $repoConfig = $container->getByType(\App\Services\RepositoryConfiguration::class);
    $linkGenerator = $container->getByType(\Nette\Application\LinkGenerator::class);

    // EntityManager musí vrátit EntityRepository (typový hint v konstruktoru)
    $repo = \Mockery::mock(\Doctrine\ORM\EntityRepository::class);
    $em = \Mockery::mock(\Doctrine\ORM\EntityManagerInterface::class);
    $em->shouldReceive('getRepository')
        ->with(\App\Model\Database\Entity\Photos::class)
        ->andReturn($repo);

    $factory = new \App\Model\IIIF\ManifestFactory($em, $repoConfig, $linkGenerator, $photoService);

    // Nastavíme chráněnou property $specimen, protože getFirstImage() ji používá
    $rp = new \ReflectionProperty(\App\Model\IIIF\ManifestFactory::class, 'specimen');
    $rp->setAccessible(true);
    $rp->setValue($factory, $specimen);

    // Zavoláme protected metodu getFirstImage() přes Reflection
    $rm = new \ReflectionMethod(\App\Model\IIIF\ManifestFactory::class, 'getFirstImage');
    $rm->setAccessible(true);
    $result = $rm->invoke($factory);

    \Tester\Assert::null($result);
});
