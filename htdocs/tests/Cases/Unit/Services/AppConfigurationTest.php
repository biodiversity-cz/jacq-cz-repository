<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Services\AppConfiguration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

test('AppConfiguration::getPlatform returns only valid values', function (): void {
    $validEnvs = ['test', 'development', 'production'];

    foreach ($validEnvs as $env) {
        $appConfig = createAppConfiguration(['environment' => $env]);
        $platform = $appConfig->getPlatform();

        Assert::same($env, $platform);
        Assert::true(in_array($platform, $validEnvs, true));
    }

    $appConfig = createAppConfiguration([]);
    Assert::null($appConfig->getPlatform());
});

test('AppConfiguration::isProduction returns true only for production', function (): void {
    $appConfig = createAppConfiguration(['environment' => 'production']);
    Assert::true($appConfig->isProduction());

    $appConfig = createAppConfiguration(['environment' => 'development']);
    Assert::false($appConfig->isProduction());

    $appConfig = createAppConfiguration(['environment' => 'test']);
    Assert::false($appConfig->isProduction());

    $appConfig = createAppConfiguration([]);
    Assert::false($appConfig->isProduction());
});

test('AppConfiguration::isSslDbConnection returns true if ssl is on', function (): void {
    $result = \Mockery::mock(Result::class);
    $result->shouldReceive('fetchOne')->once()->andReturn('on');

    $connection = \Mockery::mock(Connection::class);
    $connection->shouldReceive('executeQuery')
        ->with('SHOW ssl;')
        ->once()
        ->andReturn($result);

    $entityManager = \Mockery::mock(EntityManagerInterface::class);
    $entityManager->shouldReceive('getConnection')->andReturn($connection);

    $service = new class([], $entityManager) extends AppConfiguration {
        public EntityManagerInterface $entityManager;
    };

    Assert::true($service->isSslDbConnection());
});

test('AppConfiguration::isSslDbConnection returns false if ssl is off', function (): void {
    $result = \Mockery::mock(Result::class);
    $result->shouldReceive('fetchOne')->once()->andReturn('off');

    $connection = \Mockery::mock(Connection::class);
    $connection->shouldReceive('executeQuery')
        ->with('SHOW ssl;')
        ->once()
        ->andReturn($result);

    $entityManager = \Mockery::mock(EntityManagerInterface::class);
    $entityManager->shouldReceive('getConnection')->andReturn($connection);

    $service = new class([], $entityManager) extends AppConfiguration {
        public EntityManagerInterface $entityManager;
    };

    Assert::false($service->isSslDbConnection());
});


test('AppConfiguration::getVersion returns value from environment if set', function (): void {
    putenv(AppConfiguration::VERSION_VARIABLE.'=1.2.3');
    $service = createAppConfiguration();

    Assert::same('1.2.3', $service->getVersion());

    putenv('APP_VERSION');
});

test('AppConfiguration::getVersion returns fallback if env is not set', function (): void {
    putenv(AppConfiguration::VERSION_VARIABLE);
    $service = createAppConfiguration();

    Assert::same('unknown version', $service->getVersion());
});


function createAppConfiguration(array $config = []): AppConfiguration {
    $em = \Mockery::mock(EntityManagerInterface::class);
    return new AppConfiguration($config, $em);
}

