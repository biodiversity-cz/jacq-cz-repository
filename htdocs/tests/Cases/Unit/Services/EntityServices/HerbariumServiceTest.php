<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services\EntityServices;

use App\Bootstrap;
use App\Model\Database\Entity\Herbaria;
use App\Security\Identity;
use App\Services\EntityServices\HerbariumService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mockery;
use Nette\Security\User;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';


test('findOneWithAcronym calls repository and returns entity or null', function () {
    $acronym = 'ABC';
    $herbaria = Mockery::mock(Herbaria::class);

    $repository = Mockery::mock(EntityRepository::class);
    $repository->shouldReceive('findOneWithAcronym')->once()->with($acronym)->andReturn($herbaria);

    $entityManager = Mockery::mock(EntityManagerInterface::class);
    $entityManager->shouldReceive('getRepository')->once()->andReturn($repository);

    $service = new HerbariumService($entityManager);

    Assert::same($herbaria, $service->findOneWithAcronym($acronym));

    $repository->shouldReceive('findOneWithAcronym')->once()->with('XYZ')->andReturn(null);
    Assert::null($service->findOneWithAcronym('XYZ'));
});

test('getCurrentUserHerbarium returns correct Herbaria entity', function () {
    $herbariumId = 123;
    $container = Bootstrap::boot()->createContainer();
    $user = $container->getByType(User::class);

    $herbaria = Mockery::mock(Herbaria::class);
    $repository = Mockery::mock(EntityRepository::class);

    $entityManager = Mockery::mock(EntityManagerInterface::class);
    $entityManager->shouldReceive('getRepository')
        ->once()
        ->with(Herbaria::class)
        ->andReturn($repository);

    $entityManager->shouldReceive('getReference')
        ->once()
        ->with(Herbaria::class, $herbariumId)
        ->andReturn($herbaria);

    $identity = new Identity(123, null, ['herbarium' => 123]);
    $user->login($identity);
    $service = new HerbariumService($entityManager);


    $result = $service->getCurrentUserHerbarium($user);
    Assert::same($herbaria, $result);
});
