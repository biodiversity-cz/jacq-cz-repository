<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Services\AppConfiguration;
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

function createAppConfiguration(array $config): AppConfiguration {
    $em = \Mockery::mock(EntityManagerInterface::class);
    return new AppConfiguration($config, $em);
}

