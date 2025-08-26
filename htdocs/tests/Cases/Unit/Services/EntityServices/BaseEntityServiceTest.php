<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services\EntityServices;

use App\Bootstrap;
use App\Model\Database\Entity\Herbaria;
use App\Security\Identity;
use App\Services\EntityServices\BaseEntityService;
use App\Services\EntityServices\HerbariumService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mockery;
use Nette\Security\User;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';

class TestEntityService extends BaseEntityService
{
    protected string $entityName = 'TestEntity';
}

class DummyEntity {}

test('BaseEntityService: findAll returns repository results', function (): void {
    $entityRepositoryMock = Mockery::mock(EntityRepository::class);
    $entityManagerMock = Mockery::mock(EntityManagerInterface::class);

    $entityManagerMock
        ->shouldReceive('getRepository')
        ->with('TestEntity')
        ->andReturn($entityRepositoryMock);

    $service = new TestEntityService($entityManagerMock);

    $entity1 = new DummyEntity();
    $entity2 = new DummyEntity();

    $entityRepositoryMock
        ->shouldReceive('findAll')
        ->once()
        ->andReturn([$entity1, $entity2]);

    $all = $service->findAll();
    Assert::same([$entity1, $entity2], $all);
});

test('BaseEntityService: findOneBy returns repository result', function (): void {
    $entityRepositoryMock = Mockery::mock(EntityRepository::class);
    $entityManagerMock = Mockery::mock(EntityManagerInterface::class);

    $entityManagerMock
        ->shouldReceive('getRepository')
        ->with('TestEntity')
        ->andReturn($entityRepositoryMock);

    $service = new TestEntityService($entityManagerMock);

    $criteria = ['id' => 123];
    $orderBy = ['name' => 'ASC'];

    $entity123 = new DummyEntity();

    $entityRepositoryMock
        ->shouldReceive('findOneBy')
        ->with($criteria, $orderBy)
        ->once()
        ->andReturn($entity123);

    $one = $service->findOneBy($criteria, $orderBy);
    Assert::same($entity123, $one);
});

test('BaseEntityService: find returns repository result', function (): void {
    $entityRepositoryMock = Mockery::mock(EntityRepository::class);
    $entityManagerMock = Mockery::mock(EntityManagerInterface::class);

    $entityManagerMock
        ->shouldReceive('getRepository')
        ->with('TestEntity')
        ->andReturn($entityRepositoryMock);

    $service = new TestEntityService($entityManagerMock);

    $entity42 = new DummyEntity();

    $entityRepositoryMock
        ->shouldReceive('find')
        ->with(42)
        ->once()
        ->andReturn($entity42);

    $found = $service->find(42);
    Assert::same($entity42, $found);
});

 register_shutdown_function(function (): void {
    Mockery::close();
});
