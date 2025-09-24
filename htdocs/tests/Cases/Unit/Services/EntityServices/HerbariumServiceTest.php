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

