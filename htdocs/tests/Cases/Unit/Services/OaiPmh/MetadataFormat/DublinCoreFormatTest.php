<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services\OaiPmh\MetadataFormat;

use App\Bootstrap;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\License;
use App\Services\OaiPmh\MetadataFormat\DublinCoreFormat;
use App\Services\RepositoryConfiguration;
use Mockery;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('DublinCoreFormat: getMetadataPrefix returns oai_dc', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $repositoryConfig = $container->getByType(RepositoryConfiguration::class);
    $format = new DublinCoreFormat($repositoryConfig);

    Assert::same('oai_dc', $format->getMetadataPrefix());
});

test('DublinCoreFormat: getSchema returns correct URL', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $repositoryConfig = $container->getByType(RepositoryConfiguration::class);
     $format = new DublinCoreFormat($repositoryConfig);

    Assert::same('http://www.openarchives.org/OAI/2.0/oai_dc.xsd', $format->getSchema());
});

test('DublinCoreFormat: getMetadataNamespace returns correct namespace', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $repositoryConfig = $container->getByType(RepositoryConfiguration::class);
    $format = new DublinCoreFormat($repositoryConfig);

    Assert::same('http://www.openarchives.org/OAI/2.0/oai_dc/', $format->getMetadataNamespace());
});

test('DublinCoreFormat: getFormatName returns descriptive name', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $repositoryConfig = $container->getByType(RepositoryConfiguration::class);
    $format = new DublinCoreFormat($repositoryConfig);

    Assert::same('Simple Dublin Core', $format->getFormatName());
});

test('DublinCoreFormat: toXml creates valid DC structure', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $repositoryConfig = $container->getByType(RepositoryConfiguration::class);

    $format = new DublinCoreFormat($repositoryConfig);
    $photo = createDetailedPhotoMock();

    $xml = $format->toXml($photo, 'oai:jacq.org:photo-123');

    Assert::same('oai_dc:dc', $xml->nodeName);
    Assert::same('http://www.openarchives.org/OAI/2.0/oai_dc/', $xml->namespaceURI);

    // Check that required namespaces are present
    Assert::true($xml->hasAttributeNS('http://www.w3.org/2000/xmlns/', 'dc'));
    Assert::same('http://purl.org/dc/elements/1.1/', $xml->getAttributeNS('http://www.w3.org/2000/xmlns/', 'dc'));
});



test('DublinCoreFormat: toXml handles empty optional fields gracefully', function (): void {
    $container = Bootstrap::boot()->createContainer();
    $repositoryConfig = $container->getByType(RepositoryConfiguration::class);

    $format = new DublinCoreFormat($repositoryConfig);
    $photo = createMinimalPhotoMock();

    $xml = $format->toXml($photo, 'oai:jacq.org:photo-456');

    Assert::same('oai_dc:dc', $xml->nodeName);
    // Should not throw any exceptions
    Assert::true($xml->hasChildNodes());
});

function createDetailedPhotoMock(): Photos
{
    $photo = Mockery::mock(Photos::class);
    $photo->shouldReceive('getId')->andReturn(789);
    $photo->shouldReceive('getSpecimenId')->andReturn('123');
    $photo->shouldReceive('getFullSpecimenId')->andReturn('PRC_000123');
    $photo->shouldReceive('getWidth')->andReturn(3000);
    $photo->shouldReceive('getHeight')->andReturn(2000);
    $photo->shouldReceive('getOriginalFilename')->andReturn('specimen_123.tif');
    $photo->shouldReceive('getArchiveFilename')->andReturn('PRC_000123_789.tif');
    $photo->shouldReceive('getJp2Filename')->andReturn('test.jp2');
    $photo->shouldReceive('getExpectedJacqPid')->andReturn('https://prc.jacq.org/PRC123');

    $createdAt = new \DateTimeImmutable('2023-01-15 10:30:00');
    $lastEdit = new \DateTime('2023-01-16 14:20:00');
    $photo->shouldReceive('getCreatedAt')->andReturn($createdAt);
    $photo->shouldReceive('getLastEditAt')->andReturn($lastEdit);

    $license = Mockery::mock(License::class);

    $herbarium = Mockery::mock(Herbaria::class);
    $herbarium->shouldReceive('getAcronym')->andReturn('PRC');
    $herbarium->shouldReceive('getFullname')->andReturn('Herbarium of Prague University');
    $herbarium->shouldReceive('getAddress')->andReturn('Prague, Czech Republic');
    $herbarium->shouldReceive('getLicense')->andReturn($license);

    $photo->shouldReceive('getHerbarium')->andReturn($herbarium);

    return $photo;
}

function createMinimalPhotoMock(): object
{
    $photo = Mockery::mock(Photos::class);
    $photo->shouldReceive('getId')->andReturn(456);
    $photo->shouldReceive('getSpecimenId')->andReturn('456');
    $photo->shouldReceive('getFullSpecimenId')->andReturn('MIN_000456');
    $photo->shouldReceive('getWidth')->andReturn(null);
    $photo->shouldReceive('getHeight')->andReturn(null);
    $photo->shouldReceive('getOriginalFilename')->andReturn(null);
    $photo->shouldReceive('getArchiveFilename')->andReturn(null);
    $photo->shouldReceive('getJp2Filename')->andReturn(null);
    $photo->shouldReceive('getExpectedJacqPid')->andReturn('https://min.jacq.org/MIN456');

    $createdAt = new \DateTimeImmutable('2023-01-01 00:00:00');
    $lastEdit = new \DateTime('2023-01-16 14:20:00');

    $photo->shouldReceive('getCreatedAt')->andReturn($createdAt);
    $photo->shouldReceive('getLastEditAt')->andReturn($lastEdit);

    $license = Mockery::mock(License::class);

    $herbarium = Mockery::mock(Herbaria::class);
    $herbarium->shouldReceive('getAcronym')->andReturn('MIN');
    $herbarium->shouldReceive('getFullname')->andReturn(null);
    $herbarium->shouldReceive('getAddress')->andReturn(null);
    $herbarium->shouldReceive('getLicense')->andReturn($license);

    $photo->shouldReceive('getHerbarium')->andReturn($herbarium);

    return $photo;
}

register_shutdown_function(function (): void {
    Mockery::close();
});
