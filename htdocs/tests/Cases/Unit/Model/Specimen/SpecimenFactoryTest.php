<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Bootstrap;
use App\Exceptions\SpecimenIdException;
use App\Model\Database\Entity\Herbaria;
use App\Model\Specimen\Specimen;
use App\Model\Specimen\SpecimenFactory;
use App\Services\EntityServices\HerbariumService;
use App\Services\SpecimenIdService;
use Nette\Security\User;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';


test('SpecimenFactory throws exception for empty id', function (): void {
    $herbariumService = \Mockery::mock(HerbariumService::class);
    $container = Bootstrap::boot()->createContainer();
    $specimenIdService = $container->getByType(SpecimenIdService::class);

    $factory = new SpecimenFactory($herbariumService, $specimenIdService);

    Assert::exception(function () use ($factory) {
        $factory->create('');
    }, SpecimenIdException::class, 'Specimen id cannot be empty');
});

test('SpecimenFactory::create builds specimen from full id', function (): void {
    $herbarium = \Mockery::mock(Herbaria::class);

    $specimenIdService = \Mockery::mock(SpecimenIdService::class);
    $specimenIdService->shouldReceive('getHerbariumFromFullId')
        ->with('PR-000123')
        ->andReturn($herbarium);
    $specimenIdService->shouldReceive('getInternalPartFromId')
        ->with('PR-000123')
        ->andReturn('123');

    $herbariumService = \Mockery::mock(HerbariumService::class);

    $factory = new SpecimenFactory($herbariumService, $specimenIdService);
    $specimen = $factory->create('PR-000123');

    Assert::type(Specimen::class, $specimen);
    Assert::same($herbarium, $specimen->herbarium);
    Assert::same('123', $specimen->id);
});

test('SpecimenFactory::createFromInternalPart builds specimen for current user herbarium', function (): void {
    $herbarium = \Mockery::mock(Herbaria::class);
    $user = \Mockery::mock(User::class);

    $herbariumService = \Mockery::mock(HerbariumService::class);
    $herbariumService->shouldReceive('getCurrentUserHerbarium')
        ->with($user)
        ->andReturn($herbarium);

    $specimenIdService = \Mockery::mock(SpecimenIdService::class);

    $factory = new SpecimenFactory($herbariumService, $specimenIdService);
    $specimen = $factory->createFromInternalPart($user, '999');

    Assert::type(Specimen::class, $specimen);
    Assert::same($herbarium, $specimen->herbarium);
    Assert::same('999', $specimen->id);
});
