<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services\OaiPmh\MetadataFormat;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\License;
use App\Services\OaiPmh\MetadataFormat\CcmmFormat;
use Mockery;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('CcmmFormat: getMetadataPrefix returns ccmm', function (): void {
    $format = new CcmmFormat();

    Assert::same('ccmm', $format->getMetadataPrefix());
});

test('CcmmFormat: getSchema returns placeholder URL', function (): void {
    $format = new CcmmFormat();

    Assert::same('http://example.com/ccmm.xsd', $format->getSchema());
});

test('CcmmFormat: getMetadataNamespace returns placeholder namespace', function (): void {
    $format = new CcmmFormat();

    Assert::same('http://example.com/ccmm/', $format->getMetadataNamespace());
});

test('CcmmFormat: getFormatName returns descriptive name', function (): void {
    $format = new CcmmFormat();

    Assert::same('CCMM Format', $format->getFormatName());
});

test('CcmmFormat: toXml creates valid CCMM structure', function (): void {
    $format = new CcmmFormat();
    $photo = createCcmmPhotoMock();

    $xml = $format->toXml($photo, 'oai:jacq.org:photo-123');

    Assert::same('ccmm:record', $xml->nodeName);
    Assert::same('http://example.com/ccmm/', $xml->namespaceURI);

    // Check that schema location is set
    Assert::true($xml->hasAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'schemaLocation'));
});



function createCcmmPhotoMock(): Photos
{
    $photo = Mockery::mock(Photos::class);
    $photo->shouldReceive('getId')->andReturn(123);
    $photo->shouldReceive('getSpecimenId')->andReturn('789');
    $photo->shouldReceive('getFullSpecimenId')->andReturn('TEST_000789');
    $photo->shouldReceive('getWidth')->andReturn(2400);
    $photo->shouldReceive('getHeight')->andReturn(1800);
    $photo->shouldReceive('getOriginalFilename')->andReturn('original.tif');

    $createdAt = new \DateTimeImmutable('2023-05-10 08:00:00');
    $lastEdit = new \DateTime('2023-05-11 10:00:00');
    $photo->shouldReceive('getCreatedAt')->andReturn($createdAt);
    $photo->shouldReceive('getLastEditAt')->andReturn($lastEdit);

    $herbarium = Mockery::mock(Herbaria::class);
    $herbarium->shouldReceive('getAcronym')->andReturn('TEST');
    $herbarium->shouldReceive('getFullname')->andReturn('Test Herbarium');

    $photo->shouldReceive('getHerbarium')->andReturn($herbarium);

    return $photo;
}

function createMinimalCcmmPhotoMock(): Photos
{
    $photo = Mockery::mock(Photos::class);
    $photo->shouldReceive('getId')->andReturn(999);
    $photo->shouldReceive('getSpecimenId')->andReturn('999');
    $photo->shouldReceive('getFullSpecimenId')->andReturn('MIN_000999');
    $photo->shouldReceive('getWidth')->andReturn(null);
    $photo->shouldReceive('getHeight')->andReturn(null);
    $photo->shouldReceive('getOriginalFilename')->andReturn(null);

    $createdAt = new \DateTimeImmutable('2023-01-01 00:00:00');
    $photo->shouldReceive('getCreatedAt')->andReturn($createdAt);
    $photo->shouldReceive('getLastEditAt')->andReturn($createdAt);

    $herbarium = Mockery::mock(Herbaria::class);
    $herbarium->shouldReceive('getAcronym')->andReturn('MIN');
    $herbarium->shouldReceive('getFullname')->andReturn(null);

    $photo->shouldReceive('getHerbarium')->andReturn($herbarium);

    return $photo;
}

register_shutdown_function(function (): void {
    Mockery::close();
});
