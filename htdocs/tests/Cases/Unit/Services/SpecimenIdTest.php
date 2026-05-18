<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Bootstrap;
use App\Exceptions\SpecimenIdException;
use App\Model\Database\Entity\Herbaria;
use App\Model\Specimen\SpecimenFactory;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use App\Services\SpecimenIdService;
use Mockery;
use Tester\Assert;

require __DIR__.'/../../../bootstrap.php';

function createService(Mockery\MockInterface $mockHerbariumService): SpecimenIdService
{
    $container = Bootstrap::boot()->createContainer();
    $mockRepoConfig = $container->getByType(RepositoryConfiguration::class);
    $mockSpecimenFactory = $container->getByType(SpecimenFactory::class);
    $mockPhotoService = $container->getByType(PhotoService::class);

    return new SpecimenIdService($mockRepoConfig, $mockHerbariumService, $mockSpecimenFactory, $mockPhotoService);
}

test('splitSpecimenId parses valid specimenId with different separators', function (): void {
    $mockHerbariumService = \Mockery::mock(HerbariumService::class);
    $service = createService($mockHerbariumService);

    $examples = [
        'ABC-12345',
        'XYZ 9876',
        'abc_456',
        'Herb–789',
    ];
    foreach ($examples as $id) {
        $result = (new \ReflectionMethod($service, 'splitSpecimenId'))->invoke($service, $id);
        Assert::same(strtoupper($result[SpecimenIdService::REGEX_HERBARIUM]), strtoupper(preg_replace('/[\s\-–_].*/', '', $id)));
        Assert::true(is_numeric($result[SpecimenIdService::REGEX_SPECIMEN]));
    }
});

test('splitSpecimenId throws on invalid specimenIds', function (): void {
    $mockHerbariumService = \Mockery::mock(HerbariumService::class);
    $service = createService($mockHerbariumService);

    $invalids = [
        'ABC12345',    // no separator
        '123-456',     // herbarium not letters
        'AB!C-123',    // invalid character in herbarium
        'ABC-',        // missing specimen part
        '-12345',      // missing herbarium
    ];

    foreach ($invalids as $id) {
        Assert::exception(
            fn () => (new \ReflectionMethod($service, 'splitSpecimenId'))->invoke($service, $id),
            SpecimenIdException::class,
            'invalid name format: '.$id
        );
    }
});

test('getInternalPartFromId returns internal part', function (): void {
    $mockHerbariumService = \Mockery::mock(HerbariumService::class);
    $service = createService($mockHerbariumService);

    $id = 'XYZ 9876';
    $num = $service->getInternalPartFromId($id);
    Assert::same('9876', $num);
});

test('getInternalPartFromId returns integer part 2nd', function (): void {
    $mockHerbariumService = \Mockery::mock(HerbariumService::class);
    $service = createService($mockHerbariumService);

    $id = 'XYZ ac98/76';
    $num = $service->getInternalPartFromId($id);
    Assert::same('ac98/76', $num);
});

test('getHerbariumFromId returns Herbaria entity if found', function (): void {
    $mockHerbariumService = \Mockery::mock(HerbariumService::class);
    $herbarium = \Mockery::mock(Herbaria::class);

    $mockHerbariumService->shouldReceive('findOneWithAcronym')->once()->with('PRC')->andReturn($herbarium);

    $service = createService($mockHerbariumService);
    $result = $service->getHerbariumFromFullId('PRC-37');

    Assert::same($herbarium, $result);
});

test('getHerbariumFromId throws if herbarium not found', function (): void {
    $mockHerbariumService = \Mockery::mock(HerbariumService::class);

    $mockHerbariumService->shouldReceive('findOneWithAcronym')->once()->with('PRC')->andReturn(null);

    $service = createService($mockHerbariumService);

    Assert::exception(
        fn () => $service->getHerbariumFromFullId('PRC 456'),
        SpecimenIdException::class,
        'Unknown herbarium'
    );
});
