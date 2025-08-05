<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Bootstrap;
use App\Model\Database\Entity\Photos;
use App\Services\RepositoryConfiguration;
use App\Services\TempDir;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

test('RepositoryConfiguration - basic config directives', function (): void {
    $service = createClassMock();

    Assert::type('string', $service->getRepositoryArchiveBucket());
    Assert::notEqual('', $service->getRepositoryArchiveBucket());

    Assert::type('string', $service->getRepositoryImageServerBucket());
    Assert::notEqual('', $service->getRepositoryImageServerBucket());

    Assert::type('string', $service->getRepositoryDatabotThumbsBucket());
    Assert::notEqual('', $service->getRepositoryDatabotThumbsBucket());

    Assert::notEqual( $service->getRepositoryArchiveBucket(), $service->getRepositoryDatabotThumbsBucket());
    Assert::notEqual( $service->getRepositoryArchiveBucket(), $service->getRepositoryImageServerBucket());
    Assert::notEqual( $service->getRepositoryImageServerBucket(), $service->getRepositoryDatabotThumbsBucket());

    Assert::type('string', $service->getImageServerInfoUrl('test'));
    Assert::notEqual('', $service->getImageServerInfoUrl(''));

    Assert::type('string', $service->getImageServerUrlThumbnail('test'));
    Assert::notEqual('', $service->getImageServerUrlThumbnail(''));

    Assert::type('int', $service->getJp2Quality());
    Assert::true($service->getJp2Quality() >= 0 && $service->getJp2Quality() <= 100);

    Assert::type('int', $service->getZbarImageSize());
    Assert::true($service->getZbarImageSize() >= 500 && $service->getZbarImageSize() <= 10000);

    Assert::type('int', $service->getThumbnailSize());
    Assert::true($service->getThumbnailSize() >= 10 && $service->getThumbnailSize() <= 10000);

    Assert::type('int', $service->getPreviewSize());
    Assert::true($service->getPreviewSize() >= 100 && $service->getPreviewSize() <= 10000);

    Assert::type('int', $service->getPreviewQuality());
    Assert::true($service->getPreviewQuality() >= 0 && $service->getPreviewQuality() <= 100);

});

test('RepositoryConfiguration::createS3Jp2Name returns correct filename', function (): void {

    $service = createClassMock();
    $photo = createPhotoMock();
    $filename = $service->createS3Jp2Name($photo);

    Assert::same('TEST_00005_54.jp2', $filename);
});

test('RepositoryConfiguration::createS3DatabotThumbName returns correct filename', function (): void {

    $service = createClassMock();
    $photo = createPhotoMock();
    $filename = $service->createS3DatabotThumbName($photo);

    Assert::same('TEST_00005_54.png', $filename);
});

test('RepositoryConfiguration::createS3TifName returns correct filename', function (): void {

    $service = createClassMock();
    $photo = createPhotoMock();
    $filename = $service->createS3TifName($photo);

    Assert::same('TEST_00005_54.tif', $filename);
});

function createPhotoMock(string $specimenId = 'TEST_00005', int $photoId = 54): Photos
{
    $photo = \Mockery::mock(Photos::class);
    $photo->shouldReceive('getFullSpecimenId')->andReturn($specimenId);
    $photo->shouldReceive('getId')->andReturn($photoId);
    return $photo;
}

function createClassMock(): RepositoryConfiguration
{
    $container = Bootstrap::boot()->createContainer();
    $tempDir = $container->getByType(TempDir::class);
    $parameters = $container->getParameters();
    return new RepositoryConfiguration($parameters["storage"], $tempDir);
}

