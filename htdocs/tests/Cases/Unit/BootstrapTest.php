<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Services;

use App\Bootstrap;
use Tester\Assert;

require_once __DIR__.'/../../bootstrap.php';

test('Bootstrap loads test.neon config when NETTE_ENV is test', function (): void {
    putenv('NETTE_ENV=test');
    $container = Bootstrap::boot()->createContainer();
    $params = $container->getParameters();
    Assert::same('test', $params['environment'] ?? null);

    putenv('NETTE_ENV'); // unset
});

test('Bootstrap loads prod.neon config by default', function (): void {
    putenv('NETTE_ENV');
    putenv('DB_PASSWORD=prod');
    $container = Bootstrap::boot()->createContainer();
    $params = $container->getParameters();
    Assert::same('production', $params['environment'] ?? null);
    putenv('NETTE_ENV'); // unset
    putenv('DB_PASSWORD');
});
