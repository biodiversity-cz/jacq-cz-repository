<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Services\ImagickService;
use Tester\Assert;

require_once __DIR__.'/../../../bootstrap.php';
// due Imagick..
$old = error_reporting();
error_reporting(E_ALL & ~E_DEPRECATED);

test('ImagickService::getLargestImageIndex selects largest image', function (): void {
    $imagick = \Mockery::mock(\Imagick::class)->shouldIgnoreMissing();
    $imagick->shouldReceive('getNumberImages')->andReturn(3);

    $imagick->shouldReceive('setIteratorIndex')->with(0);
    $imagick->shouldReceive('setIteratorIndex')->with(1);
    $imagick->shouldReceive('setIteratorIndex')->with(2);

    $imagick->shouldReceive('getImageWidth')->andReturn(100, 50, 300);
    $imagick->shouldReceive('getImageHeight')->andReturn(200, 50, 300);

    $service = new ImagickService();
    Assert::same(2, $service->getLargestImageIndex($imagick));
});

test('ImagickService::resizeImage downsizes correctly for wide and tall images', function (): void {
    $imagick = \Mockery::mock(\Imagick::class)->shouldIgnoreMissing();

    // Wide image (šířka > výška)
    $imagick->shouldReceive('getImageWidth')->andReturn(2000);
    $imagick->shouldReceive('getImageHeight')->andReturn(1000);
    $imagick->shouldReceive('resizeImage')
        ->once()
        ->with(1000, 500, \Imagick::FILTER_GAUSSIAN, 1);

    $service = new ImagickService();
    $result = $service->resizeImage($imagick, 1000);
    Assert::same($imagick, $result);

    // Tall image (výška > šířka)
    $imagickTall = \Mockery::mock(\Imagick::class)->shouldIgnoreMissing();
    $imagickTall->shouldReceive('getImageWidth')->andReturn(800);
    $imagickTall->shouldReceive('getImageHeight')->andReturn(1600);
    $imagickTall->shouldReceive('resizeImage')
        ->once()
        ->with(500, 1000, \Imagick::FILTER_GAUSSIAN, 1);

    $resultTall = $service->resizeImage($imagickTall, 1000);
    Assert::same($imagickTall, $resultTall);
});

test('ImagickService::preparePngThumb applies PNG settings', function (): void {
    $imagick = \Mockery::mock(\Imagick::class)->shouldIgnoreMissing();

    $imagick->shouldReceive('getImageWidth')->andReturn(100);
    $imagick->shouldReceive('getImageHeight')->andReturn(100);
    $imagick->shouldReceive('resizeImage')->never();

    $imagick->shouldReceive('setImageFormat')->once()->with('png');
    $imagick->shouldReceive('setImageCompression')->once()->with(\Imagick::COMPRESSION_ZIP);
    $imagick->shouldReceive('setImageCompressionQuality')->once()->with(90);
    $imagick->shouldReceive('setImageDepth')->once()->with(8);
    $imagick->shouldReceive('stripImage')->once();

    $service = new ImagickService();
    $result = $service->preparePngThumb($imagick, 640);

    Assert::same($imagick, $result);
});

test('ImagickService::readIdentify parses rawOutput', function (): void {
    $imagick = \Mockery::mock(\Imagick::class)->shouldIgnoreMissing();
    $exampleIdentify = [
        'imageName' => '/IMG_0558_01.jpg',
        'mimetype' => 'image/jpeg',
        'format' => 'JPEG (Joint Photographic Experts Group JFIF format)',
        'units' => 'Undefined',
        'colorSpace' => 'sRGB',
        'type' => 'TrueColor',
        'compression' => 'JPEG',
        'fileSize' => '5.0602MiB',
        'geometry' => [
            'width' => 3906,
            'height' => 2602,
        ],
        'resolution' => [
            'x' => 0.0,
            'y' => 0.0,
        ],
        'signature' => 'cd60a81df1e180bba8dd0cf1b8622223d43e6234436aad9cea0ad7f10ece432a',
        'rawOutput' => 'Image:
  Filename: /IMG_0558_01.jpg
  Base filename: IMG_0558_01.jpg
  Permissions: rwxr-xr-x
  Format: JPEG (Joint Photographic Experts Group JFIF format)
  Mime type: image/jpeg
  Class: DirectClass
  Geometry: 3906x2602+0+0
  Units: Undefined
  Colorspace: sRGB
  Type: TrueColor
  Base type: Undefined
  Endianness: Undefined
  Depth: 8-bit
  Channel depth:
    red: 8-bit
    green: 8-bit
    blue: 8-bit
  Channel statistics:
    Pixels: 10163412
    Red:
      min: 10  (0.0392157)
      max: 254 (0.996078)
      mean: 73.5187 (0.288309)
      standard deviation: 17.623 (0.0691098)
      kurtosis: 34.1699
      skewness: 5.02741
      entropy: 0.655766
    Green:
      min: 12  (0.0470588)
      max: 221 (0.866667)
      mean: 87.0023 (0.341186)
      standard deviation: 12.912 (0.0506352)
      kurtosis: 22.6896
      skewness: 3.15591
      entropy: 0.674387
    Blue:
      min: 0  (0)
      max: 190 (0.745098)
      mean: 55.9469 (0.2194)
      standard deviation: 12.7907 (0.0501597)
      kurtosis: 2.35432
      skewness: 0.796377
      entropy: 0.751178
  Image statistics:
    Overall:
      min: 0  (0)
      max: 254 (0.996078)
      mean: 72.156 (0.282965)
      standard deviation: 14.4419 (0.0566349)
      kurtosis: 9.98642
      skewness: 1.63821
      entropy: 0.693777
  Rendering intent: Perceptual
  Gamma: 0.454545
  Chromaticity:
    red primary: (0.64,0.33,0.03)
    green primary: (0.3,0.6,0.1)
    blue primary: (0.15,0.06,0.79)
    white point: (0.3127,0.329,0.3583)
  Background color: white
  Border color: srgb(223,223,223)
  Matte color: grey74
  Transparent color: black
  Interlace: None
  Intensity: Undefined
  Compose: Over
  Page geometry: 3906x2602+0+0
  Dispose: Undefined
  Iterations: 0
  Compression: JPEG
  Quality: 100
  Orientation: Undefined
  Profiles:
    Profile-exif: 42154 bytes
  Properties:
    date:create: 2023-02-01T10:04:52+00:00
    date:modify: 2010-05-23T17:17:28+00:00
    date:timestamp: 2025-08-26T12:28:32+00:00
    exif:ApertureValue: 327680/65536
    exif:ColorSpace: 1
    exif:ComponentsConfiguration: ...
    exif:CustomRendered: 0
    exif:DateTime: 2010:05:22 13:25:32
    exif:DateTimeDigitized: 2010:05:22 13:25:32
    exif:DateTimeOriginal: 2010:05:22 13:25:32
    exif:ExifOffset: 130
    exif:ExifVersion: 0221
    exif:ExposureBiasValue: 0/1
    exif:ExposureMode: 0
    exif:ExposureProgram: 4
    exif:ExposureTime: 1/200
    exif:Flash: 16
    exif:FlashPixVersion: 0100
    exif:FNumber: 56/10
    exif:FocalLength: 90/1
    exif:FocalPlaneResolutionUnit: 2
    exif:FocalPlaneXResolution: 3888000/876
    exif:FocalPlaneYResolution: 2592000/583
    exif:InteroperabilityOffset: 37012
    exif:Make: Canon
    exif:MakerNote: "
    exif:MeteringMode: 6
    exif:Model: Canon EOS 1000D
    exif:PhotographicSensitivity: 100
    exif:PixelXDimension: 3888
    exif:PixelYDimension: 2592
    exif:SceneCaptureType: 0
    exif:ShutterSpeedValue: 499712/65536
    exif:SubSecTime: 85
    exif:SubSecTimeDigitized: 85
    exif:SubSecTimeOriginal: 85
    exif:thumbnail:InteroperabilityIndex: R98
    exif:thumbnail:InteroperabilityVersion: 0100
    exif:thumbnail:JPEGInterchangeFormat: 37072
    exif:thumbnail:JPEGInterchangeFormatLength: 5076
    exif:UserComment:
    exif:WhiteBalance: 0
    jpeg:colorspace: 2
    jpeg:sampling-factor: 2x2,1x1,1x1
    signature: cd60a81df1e180bba8dd0cf1b8622223d43e6234436aad9cea0ad7f10ece432a
    unknown: Rawstudio 1.2
  Tainted: False
  Filesize: 5.0602MiB
  Number pixels: 10.1634M
  Pixels per second: 90.0166MB
  User time: 0.110u
  Elapsed time: 0:01.112
  Version: ImageMagick 6.9.12-98 Q16 x86_64 18038 https://legacy.imagemagick.org
',
    ];
    $imagick->shouldReceive('identifyImage')->andReturn($exampleIdentify);

    $service = new ImagickService();
    $identify = $service->readIdentify($imagick);

    Assert::true(isset($identify['type']));
    if (isset($identify['type'])) {
        Assert::same('TrueColor', $identify['type']);
    } else {
        Assert::same('TrueColor', $identify['type']);
    }
});

test('ImagickService::readExif returns properties', function (): void {
    $imagick = \Mockery::mock(\Imagick::class)->shouldIgnoreMissing();
    $imagick->shouldReceive('getImageProperties')->andReturn([
        'EXIF:Make' => 'Canon',
        'EXIF:Model' => 'EOS',
    ]);

    $service = new ImagickService();
    $exif = $service->readExif($imagick);

    Assert::same('Canon', $exif['EXIF:Make']);
});

test('ImagickService::sanitizeUtf8 cleans invalid UTF8', function (): void {
    $service = new ImagickService();

    $method = new \ReflectionMethod(ImagickService::class, 'sanitizeUtf8');

    $clean = $method->invoke($service, "valid\xFFtext");

    Assert::contains('valid', $clean);
    Assert::notContains("\xFF", $clean);
});

error_reporting($old);
