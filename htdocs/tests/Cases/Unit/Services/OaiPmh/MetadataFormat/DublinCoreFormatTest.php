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
require_once 'photoMocks.php';

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

register_shutdown_function(function (): void {
    Mockery::close();
});
