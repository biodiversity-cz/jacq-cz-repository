<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\FileManagement\File;
use App\Model\FileManagement\FileInsideCuratorBucket;
use Aws\Api\DateTimeResult;
use Aws\Result;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';


test('FileInsideCuratorBucket::getUploaded formats timestamp correctly', function (): void {
    $timestamp = new DateTimeResult('2025-08-28T12:00:00+00:00');
    $file = new FileInsideCuratorBucket('image.tif', 10 * 1024 * 1024, $timestamp, false, false, null, null);

    Assert::equal('28. August 2025', $file->getUploaded());
});

test('FileInsideCuratorBucket::isSizeOk returns true within limits', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', FileInsideCuratorBucket::MIN_FILESIZE, $timestamp, false, false, null, null);

    Assert::true($file->isSizeOk());
});

test('FileInsideCuratorBucket::isSizeOk returns false when too small', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', FileInsideCuratorBucket::MIN_FILESIZE - 1, $timestamp, false, false, null, null);

    Assert::false($file->isSizeOk());
});

test('FileInsideCuratorBucket::isSizeOk returns false when too large', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', FileInsideCuratorBucket::MAX_FILESIZE + 1, $timestamp, false, false, null, null);

    Assert::false($file->isSizeOk());
});

test('FileInsideCuratorBucket::isTypeOk returns true for correct extension', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('photo.tif', 10 * 1024 * 1024, $timestamp, false, false, null, null);

    Assert::true($file->isTypeOk());
});

test('FileInsideCuratorBucket::isTypeOk returns false for wrong extension', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('document.jpg', 10 * 1024 * 1024, $timestamp, false, false, null, null);

    Assert::false($file->isTypeOk());
});

test('FileInsideCuratorBucket::isAlreadyWaiting reflects constructor value', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', 10 * 1024 * 1024, $timestamp, true, false, null, null);

    Assert::true($file->isAlreadyWaiting());
});

test('FileInsideCuratorBucket::hasControlError reflects constructor value', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', 10 * 1024 * 1024, $timestamp, false, true, null, null);

    Assert::true($file->hasControlError());
});

test('FileInsideCuratorBucket::getControlMsg returns message if set', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', 10 * 1024 * 1024, $timestamp, false, false, null, 'bad checksum');

    Assert::equal('bad checksum', $file->getControlMsg());
});

test('FileInsideCuratorBucket::isEligibleToBeImported true when all checks pass', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', 10 * 1024 * 1024, $timestamp, false, false, null, null);

    Assert::true($file->isEligibleToBeImported());
});

test('FileInsideCuratorBucket::isEligibleToBeImported false when already waiting', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', 10 * 1024 * 1024, $timestamp, true, false, null, null);

    Assert::false($file->isEligibleToBeImported());
});

test('FileInsideCuratorBucket::isEligibleToBeImported false when has control error', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', 10 * 1024 * 1024, $timestamp, false, true, null, 'error');

    Assert::false($file->isEligibleToBeImported());
});

test('FileInsideCuratorBucket::hasPrecontrolError true if size invalid', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', 1, $timestamp, false, false, null, null);

    Assert::true($file->hasPrecontrolError());
});

test('FileInsideCuratorBucket::hasPrecontrolError true if type invalid', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.jpg', 10 * 1024 * 1024, $timestamp, false, false, null, null);

    Assert::true($file->hasPrecontrolError());
});

test('FileInsideCuratorBucket::setIneligibleForImport forces ineligibility', function (): void {
    $timestamp = new DateTimeResult('now');
    $file = new FileInsideCuratorBucket('image.tif', 10 * 1024 * 1024, $timestamp, false, false, null, null);

    Assert::true($file->isEligibleToBeImported());

    $file->setIneligibleForImport();
    Assert::false($file->isEligibleToBeImported());
});
