<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Bootstrap;
use App\Exceptions\ConfigurationException;
use App\Services\RepositoryConfiguration;
use App\Services\TempDir;
use Tester\Assert;
use Tests\Toolkit\PhotoTestFactory;

require_once __DIR__.'/../../../bootstrap.php';

test('RepositoryConfiguration - basic config directives', function (): void {
    $service = createClassMock();

    Assert::type('string', $service->getArchiveBucket(PhotoTestFactory::minimal()));
    Assert::notEqual('', $service->getArchiveBucket(PhotoTestFactory::minimal()));

    Assert::type('string', $service->getImageServerBucket(PhotoTestFactory::minimal()));
    Assert::notEqual('', $service->getImageServerBucket(PhotoTestFactory::minimal()));

    Assert::type('string', $service->getDatabotThumbsBucket(PhotoTestFactory::minimal()));
    Assert::notEqual('', $service->getDatabotThumbsBucket(PhotoTestFactory::minimal()));

    Assert::notEqual($service->getArchiveBucket(PhotoTestFactory::minimal()), $service->getDatabotThumbsBucket(PhotoTestFactory::minimal()));
    Assert::notEqual($service->getArchiveBucket(PhotoTestFactory::minimal()), $service->getImageServerBucket(PhotoTestFactory::minimal()));
    Assert::notEqual($service->getImageServerBucket(PhotoTestFactory::minimal()), $service->getDatabotThumbsBucket(PhotoTestFactory::minimal()));

    Assert::type('string', $service->getImageServerInfoUrl(PhotoTestFactory::minimal()));

    Assert::type('string', $service->getImageServerUrlThumbnail(PhotoTestFactory::minimal()));

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
    $photo = PhotoTestFactory::minimal();
    $filename = $service->createS3Jp2Name($photo);

    Assert::same('TEST_000005_1.jp2', $filename);
});

test('RepositoryConfiguration::createS3DatabotThumbName returns correct filename', function (): void {
    $service = createClassMock();
    $photo = PhotoTestFactory::minimal();
    $filename = $service->createS3DatabotThumbName($photo);

    Assert::same('TEST_000005_1.png', $filename);
});

test('RepositoryConfiguration::createS3TifName returns correct filename', function (): void {
    $service = createClassMock();
    $photo = PhotoTestFactory::minimal();
    $filename = $service->createS3TifName($photo);

    Assert::same('TEST_000005_1.tif', $filename);
});

test('RepositoryConfiguration throws exception for missing key', function (): void {
    $tempDir = \Mockery::mock(TempDir::class);
    $config = [];
    $service = new RepositoryConfiguration($config, $tempDir);

    Assert::exception(
        fn () => $service->getRecentlyUsedArchiveBucket(),
        ConfigurationException::class,
        'Archive bucket prefix not set.'
    );
});

function createClassMock(): RepositoryConfiguration
{
    $container = Bootstrap::boot()->createContainer();
    $tempDir = $container->getByType(TempDir::class);
    $parameters = $container->getParameters();

    return new RepositoryConfiguration($parameters['storage'], $tempDir);
}
