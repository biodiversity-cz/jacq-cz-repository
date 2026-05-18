<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Databot;
use App\Model\Database\Enums\DatabotRole;
use Tester\Assert;

require_once __DIR__.'/../../../../../bootstrap.php';

test('Databot entity getters, setters and traits', function (): void {
    $databot = new Databot();

    // Traits TId + TCreatedAt přes reflection
    $refId = new \ReflectionProperty($databot, 'id');

    $refCreatedAt = new \ReflectionProperty($databot, 'createdAt');

    // nastavíme id, createdAt (předpoklad)
    $refId->setValue($databot, 42);
    $refCreatedAt->setValue($databot, new \DateTimeImmutable('2025-01-01T00:00:00+00:00'));

    Assert::same(42, $databot->id);
    Assert::equal(new \DateTimeImmutable('2025-01-01T00:00:00+00:00'), $databot->createdAt);

    // Test name
    $databot->setName('BotName');
    Assert::same('BotName', $databot->name);

    // Test description
    $databot->setDescription('Some description');
    Assert::same('Some description', $databot->description);

    // Test version
    $databot->setVersion(5);
    Assert::same(5, $databot->version);

    // Test enabled
    $databot->setEnabled(false);
    Assert::false($databot->enabled);

    $databot->setEnabled(true);
    Assert::true($databot->enabled);

    // Test lastRun
    $dt = new \DateTimeImmutable('2024-12-31 12:00:00');
    $databot->setLastRun($dt);
    Assert::equal($dt, $databot->lastRun);

    $databot->setLastRun(null);
    Assert::null($databot->lastRun);

    $role = DatabotRole::VALIDATOR;
    $databot->setRole($role);
    Assert::same($role, $databot->role);
});
