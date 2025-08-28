<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\FileManagement\File;
use App\Model\FileManagement\FileInsideCuratorBucket;
use Aws\Result;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';


test('File::getUploaded formats LastModified correctly', function (): void {
    $lastModified = new \DateTime('2025-08-28');
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('LastModified')->andReturn($lastModified);

    $file = new File('test.txt', $info, false);
    Assert::equal('28. August 2025', $file->getUploaded());
});

test('File::getCreated returns formatted origin-date-iso8601', function (): void {
    $metadata = ['origin-date-iso8601' => '2025-08-27T12:00:00+00:00'];
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('Metadata')->andReturn($metadata);

    $file = new File('test.txt', $info, false);
    Assert::equal('27. August 2025', $file->getCreated());
});

test('File::getCreated returns "unknown" if no origin-date-iso8601', function (): void {
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('Metadata')->andReturn([]);

    $file = new File('test.txt', $info, false);
    Assert::equal('unknown', $file->getCreated());
});

test('File::getCreatedTimestamp returns DateTimeImmutable for origin-date-iso8601', function (): void {
    $metadata = ['origin-date-iso8601' => '2025-08-27T12:00:00+00:00'];
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('Metadata')->andReturn($metadata);

    $file = new File('test.txt', $info, false);
    $ts = $file->getCreatedTimestamp();
    Assert::type(\DateTimeImmutable::class, $ts);
    Assert::equal('2025-08-27T12:00:00+00:00', $ts->format('c'));
});

test('File::getCreatedTimestamp returns null if no origin-date-iso8601', function (): void {
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('Metadata')->andReturn([]);

    $file = new File('test.txt', $info, false);
    Assert::null($file->getCreatedTimestamp());
});

test('File::getSize returns ContentLength as int', function (): void {
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('ContentLength')->andReturn('12345');

    $file = new File('test.txt', $info, false);
    Assert::equal(12345, $file->getSize());
});

test('File::isSizeOk returns true for size within limits', function (): void {
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('ContentLength')->andReturn(FileInsideCuratorBucket::MIN_FILESIZE);

    $file = new File('test.txt', $info, false);
    Assert::true($file->isSizeOk());
});

test('File::isTypeOk returns true if MIME_TYPE matches', function (): void {
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('ContentType')->andReturn(FileInsideCuratorBucket::MIME_TYPE);

    $file = new File('test.txt', $info, false);
    Assert::true($file->isTypeOk());
});

test('File::isAlreadyWaiting returns true if alreadyWaiting is true', function (): void {
    $info = \Mockery::mock(Result::class);
    $file = new File('test.txt', $info, true);
    Assert::true($file->isAlreadyWaiting());
});

test('File::isEligibleToBeImported returns true if all checks pass', function (): void {
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('ContentLength')->andReturn(FileInsideCuratorBucket::MIN_FILESIZE);
    $info->shouldReceive('get')->with('ContentType')->andReturn(FileInsideCuratorBucket::MIME_TYPE);

    $file = new File('test.txt', $info, false);
    Assert::true($file->isEligibleToBeImported());
});

test('File::isEligibleToBeImported returns false if size not ok', function (): void {
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('ContentLength')->andReturn(FileInsideCuratorBucket::MIN_FILESIZE - 1);
    $info->shouldReceive('get')->with('ContentType')->andReturn(FileInsideCuratorBucket::MIME_TYPE);

    $file = new File('test.txt', $info, false);
    Assert::false($file->isEligibleToBeImported());
});

test('File::isEligibleToBeImported returns false if type not ok', function (): void {
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('ContentLength')->andReturn(FileInsideCuratorBucket::MIN_FILESIZE);
    $info->shouldReceive('get')->with('ContentType')->andReturn('wrong/type');

    $file = new File('test.txt', $info, false);
    Assert::false($file->isEligibleToBeImported());
});

test('File::isEligibleToBeImported returns false if already waiting', function (): void {
    $info = \Mockery::mock(Result::class);
    $info->shouldReceive('get')->with('ContentLength')->andReturn(FileInsideCuratorBucket::MIN_FILESIZE);
    $info->shouldReceive('get')->with('ContentType')->andReturn(FileInsideCuratorBucket::MIME_TYPE);

    $file = new File('test.txt', $info, true);
    Assert::false($file->isEligibleToBeImported());
});

\Mockery::close();
