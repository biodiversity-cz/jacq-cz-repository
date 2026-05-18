<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Services\EntityServices;

use App\Model\Database\Entity\Maintenance;
use App\Services\EntityServices\MaintenanceService;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Tester\Assert;

require_once __DIR__.'/../../../../bootstrap.php';

// Dummy entity pro test
class DummyMaintenance extends Maintenance
{
}

test('MaintenanceService: getValid returns repository result', function (): void {
    $repositoryMock = \Mockery::mock(\App\Model\Database\Repository\AbstractRepository::class);
    $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);

    $entityManagerMock
        ->shouldReceive('getRepository')
        ->with(Maintenance::class)
        ->andReturn($repositoryMock);

    $service = new MaintenanceService($entityManagerMock);

    $valid1 = new DummyMaintenance();
    $valid2 = new DummyMaintenance();

    $repositoryMock
        ->shouldReceive('getValid')
        ->once()
        ->andReturn([$valid1, $valid2]);

    $result = $service->getValid();
    Assert::same([$valid1, $valid2], $result);
});

// Ukončení Mockery
register_shutdown_function(function (): void {
    \Mockery::close();
});
