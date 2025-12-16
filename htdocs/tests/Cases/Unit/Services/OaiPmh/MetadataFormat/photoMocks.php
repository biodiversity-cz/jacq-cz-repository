<?php

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\License;
use App\Model\Database\Entity\Photos;

function createDetailedPhotoMock(): Photos
{
    $photo = new Photos();

    $set = static function (object $object, string $property, mixed $value): void {
        $ref = new \ReflectionProperty($object, $property);
        $ref->setAccessible(true);
        $ref->setValue($object, $value);
    };

    // --- Photo ---
    $set($photo, 'id', 789);
    $set($photo, 'specimenId', '123');
    $set($photo, 'fullSpecimenId', 'PRC_000123');
    $set($photo, 'width', 3000);
    $set($photo, 'height', 2000);
    $set($photo, 'originalFilename', 'specimen_123.tif');
    $set($photo, 'archiveFilename', 'PRC_000123_789.tif');
    $set($photo, 'jp2Filename', 'test.jp2');
    $set($photo, 'expectedJacqPid', 'https://prc.jacq.org/PRC123');

    $set($photo, 'createdAt', new \DateTimeImmutable('2023-01-15 10:30:00'));
    $set($photo, 'lastEdit', new \DateTime('2023-01-16 14:20:00'));

    // --- License ---
    $license = new License();

    // --- Herbarium ---
    $herbarium = new Herbaria();
    $set($herbarium, 'acronym', 'PRC');
    $set($herbarium, 'fullname', 'Herbarium of Prague University');
    $set($herbarium, 'address', 'Prague, Czech Republic');
    $set($herbarium, 'license', $license);

    $set($photo, 'herbarium', $herbarium);

    return $photo;
}

function createMinimalPhotoMock(): Photos
{
    $photo = new Photos();

    $set = static function (object $object, string $property, mixed $value): void {
        $ref = new \ReflectionProperty($object, $property);
        $ref->setAccessible(true);
        $ref->setValue($object, $value);
    };

    // --- Photo ---
    $set($photo, 'id', 456);
    $set($photo, 'specimenId', '456');
    $set($photo, 'fullSpecimenId', 'MIN_000456');

    $set($photo, 'width', null);
    $set($photo, 'height', null);
    $set($photo, 'originalFilename', null);
    $set($photo, 'archiveFilename', null);
    $set($photo, 'jp2Filename', null);
    $set($photo, 'expectedJacqPid', 'https://min.jacq.org/MIN456');

    $set($photo, 'createdAt', new \DateTimeImmutable('2023-01-01 00:00:00'));
    $set($photo, 'lastEditAt', new \DateTime('2023-01-16 14:20:00'));

    // --- License ---
    $license = new License();

    // --- Herbarium ---
    $herbarium = new Herbaria();
    $set($herbarium, 'acronym', 'MIN');
    $set($herbarium, 'fullname', null);
    $set($herbarium, 'address', null);
    $set($herbarium, 'license', $license);

    $set($photo, 'herbarium', $herbarium);

    return $photo;
}
