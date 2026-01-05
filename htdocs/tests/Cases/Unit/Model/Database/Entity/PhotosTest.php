<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Databot;
use App\Model\Database\Entity\DatabotResult;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\ImportError;
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

    $refCreatedAt = new \ReflectionProperty($photos, 'createdAt');

    $refLastEdit = new \ReflectionProperty($photos, 'lastEdit');

    $refOriginalFileAt = new \ReflectionProperty($photos, 'originalFileTimestamp');

    $refId->setValue($photos, 42);
    $refCreatedAt->setValue($photos, new \DateTimeImmutable('2025-08-06 12:00:00'));
    $refLastEdit->setValue($photos, new \DateTime('2025-08-05 11:00:00'));
    $refOriginalFileAt->setValue($photos, new \DateTimeImmutable('2025-08-04 10:00:00'));

    Assert::same(42, $photos->id);
    Assert::equal(new \DateTimeImmutable('2025-08-06 12:00:00'), $photos->createdAt);
    Assert::equal(new \DateTime('2025-08-05 11:00:00'), $photos->lastEdit);
    Assert::equal(new \DateTimeImmutable('2025-08-04 10:00:00'), $photos->originalFileTimestamp);

    $dt = new \DateTimeImmutable('2025-08-06 15:00:00');
    $return = $photos->setOriginalFileAt($dt);

    Assert::same($photos, $return);
    Assert::same($dt, $photos->originalFileTimestamp);

    $photos->setOriginalFileAt(null);
    Assert::null($photos->originalFileTimestamp);

    // Test běžných setterů a getterů
    Assert::null($photos->archiveFilename);
    $photos->setArchiveFilename('archive.tif');
    Assert::same('archive.tif', $photos->archiveFilename);

    Assert::null($photos->jp2Filename);
    $photos->setJp2Filename('image.jp2');
    Assert::same('image.jp2', $photos->jp2Filename);

    Assert::null($photos->width);
    $photos->setWidth(1920);
    Assert::same(1920, $photos->width);

    Assert::null($photos->height);
    $photos->setHeight(1080);
    Assert::same(1080, $photos->height);

    Assert::null($photos->archiveFileSize);
    $photos->setArchiveFileSize(123456);
    Assert::same(123456, $photos->archiveFileSize);

    Assert::null($photos->JP2FileSize);
    $photos->setJp2FileSize(654321);
    Assert::same(654321, $photos->JP2FileSize);

    Assert::null($photos->exif);
    $exif = ['Camera' => 'Canon', 'ISO' => 100];
    $photos->setExif($exif);
    Assert::same($exif, $photos->exif);

    Assert::null($photos->identify);
    $identify = ['verbose' => 'some metadata'];
    $photos->setIdentify($identify);
    Assert::same($identify, $photos->identify);

    // Test herbarium, status, type, error (tyto musí být objekty)
    $herbarium = new Herbaria();
    $photos->setHerbarium($herbarium);
    Assert::same($herbarium, $photos->herbarium);

    $status = new PhotosStatus();
    $photos->setStatus($status);
    Assert::same($status, $photos->status);

    $type = new PhotosType();
    $photos->setType($type);
    Assert::same($type, $photos->type);

    $photos->addImportError();
    Assert::type(ImportError::class, $photos->error);

    // Test specimenId a getFullSpecimenId, getExpectedJacqPid (nutno mít nastavené herbarium s akronymem)
    $herbariumReflection = new \ReflectionClass(Herbaria::class);
    $acronymProp = $herbariumReflection->getProperty('acronym');

    $acronymProp->setValue($herbarium, 'ABC');

    $photos->setSpecimenId('000123');
    Assert::same('123', $photos->specimenId);
    Assert::same('ABC_000123', $photos->getFullSpecimenId());
    Assert::same('https://abc.jacq.org/ABC123', $photos->getExpectedJacqPid());

    // Test původního jména souboru
    $photos->setOriginalFilename('original.jpg');
    Assert::same('original.jpg', $photos->originalFilename);

    // Test kolekce databotResults
    $databotResults = new ArrayCollection();
    $databotResult = new DatabotResult();
    $databotResults->add($databotResult);
    $photos->setDatabotResults($databotResults);
    Assert::same($databotResults, $photos->databotResults);

    // Test databotThumbFilename
    Assert::null($photos->databotThumbFilename);
    $photos->setDatabotThumbFilename('thumb.png');
    Assert::same('thumb.png', $photos->databotThumbFilename);
});
