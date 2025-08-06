<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Databot;
use App\Model\Database\Entity\DatabotResult;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosError;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Database\Entity\PhotosType;
use App\Model\Database\Entity\UserRole;
use App\Model\Database\Enums\DatabotRole;
use Doctrine\Common\Collections\ArrayCollection;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('Photos entity getters and setters', function (): void {
    $photos = new Photos();

    // Reflection pro chráněné vlastnosti z traitů TId a TCreatedAt, TLastEditAt, TOriginalFileAt
    $refId = new \ReflectionProperty($photos, 'id');
    $refId->setAccessible(true);
    $refCreatedAt = new \ReflectionProperty($photos, 'createdAt');
    $refCreatedAt->setAccessible(true);
    $refLastEdit = new \ReflectionProperty($photos, 'lastEdit');
    $refLastEdit->setAccessible(true);
    $refOriginalFileAt = new \ReflectionProperty($photos, 'originalFileTimestamp');
    $refOriginalFileAt->setAccessible(true);


    $refId->setValue($photos, 42);
    $refCreatedAt->setValue($photos, new \DateTimeImmutable('2025-08-06 12:00:00'));
    $refLastEdit->setValue($photos, new \DateTime('2025-08-05 11:00:00'));
    $refOriginalFileAt->setValue($photos, new \DateTimeImmutable('2025-08-04 10:00:00'));

    Assert::same(42, $photos->getId());
    Assert::equal(new \DateTimeImmutable('2025-08-06 12:00:00'), $photos->getCreatedAt());
    Assert::equal(new \DateTime('2025-08-05 11:00:00'), $photos->getLastEditAt());
    Assert::equal(new \DateTimeImmutable('2025-08-04 10:00:00'), $photos->getOriginalFileAt());

    $dt = new \DateTimeImmutable('2025-08-06 15:00:00');
    $return = $photos->setOriginalFileAt($dt);

    Assert::same($photos, $return);
    Assert::same($dt, $photos->getOriginalFileAt());

    $photos->setOriginalFileAt(null);
    Assert::null($photos->getOriginalFileAt());

    // Test běžných setterů a getterů
    Assert::null($photos->getArchiveFilename());
    $photos->setArchiveFilename('archive.tif');
    Assert::same('archive.tif', $photos->getArchiveFilename());

    Assert::null($photos->getJp2Filename());
    $photos->setJp2Filename('image.jp2');
    Assert::same('image.jp2', $photos->getJp2Filename());

    Assert::null($photos->getWidth());
    $photos->setWidth(1920);
    Assert::same(1920, $photos->getWidth());

    Assert::null($photos->getHeight());
    $photos->setHeight(1080);
    Assert::same(1080, $photos->getHeight());

    Assert::null($photos->getArchiveFileSize());
    $photos->setArchiveFileSize(123456);
    Assert::same(123456, $photos->getArchiveFileSize());

    Assert::null($photos->getJp2FileSize());
    $photos->setJp2FileSize(654321);
    Assert::same(654321, $photos->getJp2FileSize());

    Assert::null($photos->getExif());
    $exif = ['Camera' => 'Canon', 'ISO' => 100];
    $photos->setExif($exif);
    Assert::same($exif, $photos->getExif());

    Assert::null($photos->getIdentify());
    $identify = ['verbose' => 'some metadata'];
    $photos->setIdentify($identify);
    Assert::same($identify, $photos->getIdentify());

    // Test herbarium, status, type, error (tyto musí být objekty)
    $herbarium = new Herbaria();
    $photos->setHerbarium($herbarium);
    Assert::same($herbarium, $photos->getHerbarium());

    $status = new PhotosStatus();
    $photos->setStatus($status);
    Assert::same($status, $photos->getStatus());

    $type = new PhotosType();
    $photos->setType($type);
    Assert::same($type, $photos->getType());

    $error = new PhotosError();
    $photos->setError($error);
    Assert::same($error, $photos->getError());

    // Test specimenId a getFullSpecimenId, getJacqPid (nutno mít nastavené herbarium s akronymem)
    $herbariumReflection = new \ReflectionClass(Herbaria::class);
    $acronymProp = $herbariumReflection->getProperty('acronym');
    $acronymProp->setAccessible(true);
    $acronymProp->setValue($herbarium, 'ABC');

    $photos->setSpecimenId('000123');
    Assert::same('123', $photos->getSpecimenId());
    Assert::same('ABC_000123', $photos->getFullSpecimenId());
    Assert::same('https://abc.jacq.org/ABC123', $photos->getJacqPid());

    // Test původního jména souboru
    $photos->setOriginalFilename('original.jpg');
    Assert::same('original.jpg', $photos->getOriginalFilename());

    // Test kolekce databotResults
    $databotResults = new ArrayCollection();
    $databotResult = new DatabotResult();
    $databotResults->add($databotResult);
    $photos->setDatabotResults($databotResults);
    Assert::same($databotResults, $photos->getDatabotResults());

    // Test databotThumbFilename
    Assert::null($photos->getDatabotThumbFilename());
    $photos->setDatabotThumbFilename('thumb.png');
    Assert::same('thumb.png', $photos->getDatabotThumbFilename());
});
