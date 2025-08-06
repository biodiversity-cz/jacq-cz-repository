<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Exceptions\S3Exception;
use App\Services\S3Service;
use Aws\Result;
use Aws\S3\S3Client;
use DateTimeImmutable;
use Mockery;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';


function createS3Service(Mockery\MockInterface $mockS3Client): S3Service
{
    return new S3Service($mockS3Client);
}

test('objectExists returns true/false based on S3Client', function (): void {
    $mock = Mockery::mock(S3Client::class);
    $mock->shouldReceive('doesObjectExist')->with('bucket', 'key')->andReturn(true);

    $service = createS3Service($mock);
    Assert::true($service->objectExists('bucket', 'key'));
});

test('putFileIfNotExists throws if file exists', function (): void {
    $mock = Mockery::mock(S3Client::class);
    $mock->shouldReceive('doesObjectExist')->with('bucket', 'key')->andReturn(true);

    $service = createS3Service($mock);

    Assert::exception(
        fn() => $service->putFileIfNotExists('bucket', 'key', __FILE__, 'text/plain'),
        S3Exception::class,
        'file key already exists'
    );
});


foreach ([
             'image/tiff' => 'putTiffIfNotExists',
             'image/jp2' => 'putJp2IfNotExists',
             'image/png' => 'putPngIfNotExists',
         ] as $expectedContentType => $method) {
    test("$method uploads with correct content type", function () use ($expectedContentType, $method): void {
        $mock = Mockery::mock(S3Client::class);

        $mock->shouldReceive('doesObjectExist')->once()->andReturn(false);
        $mock->shouldReceive('putObject')->once()
            ->with(Mockery::on(fn($arg) => $arg['ContentType'] === $expectedContentType))
            ->andReturn(new Result(['ok' => true]));
        $mock->shouldReceive('headObject')->once()->andReturn(new Result(['ContentLength' => filesize(__FILE__)]));

        $service = createS3Service($mock);
        $result = $service->$method('bucket', 'key', __FILE__);
        Assert::type(Result::class, $result);
    });
}


test('putFileIfNotExists uploads file and checks size success', function (): void {
    $mock = Mockery::mock(S3Client::class);

    $mock->shouldReceive('doesObjectExist')->once()->with('bucket', 'key')->andReturn(false);
    $mock->shouldReceive('putObject')->once()->with(Mockery::on(function ($arg) {
        return $arg['Bucket'] === 'bucket' && $arg['Key'] === 'key' && $arg['ContentType'] === 'text/plain' && file_exists($arg['SourceFile']);
    }))->andReturn(new Result(['foo' => 'bar']));

    $mock->shouldReceive('headObject')->once()->with(['Bucket' => 'bucket', 'Key' => 'key'])
        ->andReturn(new Result(['ContentLength' => filesize(__FILE__)]));

    $service = createS3Service($mock);

    $result = $service->putFileIfNotExists('bucket', 'key', __FILE__, 'text/plain');
    Assert::type(Result::class, $result);
});

test('putFileIfNotExists uploads file but size mismatch throws and deletes', function (): void {
    $mock = Mockery::mock(S3Client::class);

    $mock->shouldReceive('doesObjectExist')->once()->with('bucket', 'key')->andReturn(false);
    $mock->shouldReceive('putObject')->once()->andReturn(new Result(['foo' => 'bar']));
    $mock->shouldReceive('headObject')->once()->andReturn(new Result(['ContentLength' => 1])); // fake wrong size
    $mock->shouldReceive('deleteObject')->once()->with(['Bucket' => 'bucket', 'Key' => 'key']);

    $service = createS3Service($mock);

    Assert::exception(
        fn() => $service->putFileIfNotExists('bucket', 'key', __FILE__, 'text/plain'),
        S3Exception::class,
        'Uploaded file size mismatch for key'
    );
});

test('getObjectSize returns content length', function (): void {
    $mock = Mockery::mock(S3Client::class);
    $mock->shouldReceive('headObject')->once()->with(['Bucket' => 'bucket', 'Key' => 'key'])
        ->andReturn(new Result(['ContentLength' => 1234]));

    $service = createS3Service($mock);
    Assert::same(1234, $service->getObjectSize('bucket', 'key'));
});

test('headObject returns Result', function (): void {
    $mock = Mockery::mock(S3Client::class);
    $result = new Result(['foo' => 'bar']);
    $mock->shouldReceive('headObject')->once()->andReturn($result);

    $service = createS3Service($mock);
    Assert::same($result, $service->headObject('bucket', 'key'));
});

test('getObjectOriginalTimestamp returns DateTimeImmutable or null', function (): void {
    $mock = Mockery::mock(S3Client::class);

    // with metadata
    $mock->shouldReceive('headObject')->once()->andReturn(new Result([
        'Metadata' => ['origin-date-iso8601' => '2023-08-05T12:34:56+00:00'],
    ]));
    $service = createS3Service($mock);
    $dt = $service->getObjectOriginalTimestamp('bucket', 'key');
    Assert::type(DateTimeImmutable::class, $dt);
    Assert::same('2023-08-05T12:34:56+00:00', $dt->format(DATE_ATOM));

    // without metadata
    $mock->shouldReceive('headObject')->once()->andReturn(new Result(['Metadata' => []]));
    $dt = $service->getObjectOriginalTimestamp('bucket', 'key');
    Assert::null($dt);
});

test('deleteObject returns Result', function (): void {
    $mock = Mockery::mock(S3Client::class);
    $result = new Result(['deleted' => true]);
    $mock->shouldReceive('deleteObject')->once()->andReturn($result);

    $service = createS3Service($mock);
    Assert::same($result, $service->deleteObject('bucket', 'key'));
});


test('getObject returns Result', function (): void {
    $mock = Mockery::mock(S3Client::class);
    $result = new Result(['body' => 'data']);
    $mock->shouldReceive('getObject')->once()->andReturn($result);

    $service = createS3Service($mock);
    Assert::same($result, $service->getObject('bucket', 'key', '/tmp/file'));
});

test('listObjectsNamesOnly returns array of keys from S3 iterator', function (): void {
    $mock = Mockery::mock(S3Client::class);
    $mock->shouldReceive('getIterator')
        ->once()
        ->with('ListObjects', ['Bucket' => 'bucket'])
        ->andReturn(new \ArrayIterator([
            ['Key' => 'file1.txt'],
            ['Key' => 'file2.txt'],
        ]));

    $service = createS3Service($mock);
    Assert::same(['file1.txt', 'file2.txt'], $service->listObjectsNamesOnly('bucket'));
});

test('listObjects returns an Iterator from S3Client', function (): void {
    $mock = Mockery::mock(S3Client::class);
    $iter = new \ArrayIterator([
        ['Key' => 'a'],
        ['Key' => 'b'],
    ]);
    $mock->shouldReceive('getIterator')
        ->once()
        ->with('ListObjects', ['Bucket' => 'bucket'])
        ->andReturn($iter);

    $service = createS3Service($mock);
    $result = $service->listObjects('bucket');
    Assert::type(\Iterator::class, $result);
    Assert::same(['Key' => 'a'], $result->current());
});


