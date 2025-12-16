<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;


use App\Model\Database\Entity\PhotosType;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('PhotosType entity getters and setters', function (): void {

    $photosType = new PhotosType();

// test set/get name
    $photosType->setName('Specimen');
    Assert::equal('Specimen', $photosType->name);

// test set/get description
    $photosType->setDescription('Photo of preserved specimen');
    Assert::equal('Photo of preserved specimen', $photosType->description);

// test set/get color
    $photosType->setColor('primary');
    Assert::equal('primary', $photosType->color);

// změna barvy
    $photosType->setColor('secondary');
    Assert::equal('secondary', $photosType->color);
});
