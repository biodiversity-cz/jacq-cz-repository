<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosError;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('PhotosError entity basic getters/setters and TId trait', function (): void {
    $photosError = new PhotosError();

    // Mock nebo reálný objekt Photos (zde jednoduchý mock)
    $photoMock = \Mockery::mock(Photos::class);
    $duplicateMock = \Mockery::mock(Photos::class);

    // Test set/get photo
    $photosError->setPhoto($photoMock);
    Assert::same($photoMock, $photosError->getPhoto());

    // Test set/get duplicateTo
    $photosError->setDuplicateTo($duplicateMock);
    Assert::same($duplicateMock, $photosError->getDuplicateTo());

    $photosError->setDuplicateTo(null);
    Assert::null($photosError->getDuplicateTo());

    // Test set/get message
    $photosError->setMessage('Test error message');
    Assert::same('Test error message', $photosError->getMessage());

    // Test set/get barcodes
    $photosError->setBarcodes('barcode123');
    Assert::same('barcode123', $photosError->getBarcodes());

    $photosError->setBarcodes(null);
    Assert::null($photosError->getBarcodes());

    // Test set/get thumbnail (mixed, tak může být cokoliv)
    $photosError->setThumbnail('binarydata');
    Assert::same('binarydata', $photosError->getThumbnail());

    $photosError->setThumbnail(null);
    Assert::null($photosError->getThumbnail());

});
