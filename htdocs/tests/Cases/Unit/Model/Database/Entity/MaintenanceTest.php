<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Maintenance;
use Tester\Assert;

require_once __DIR__.'/../../../../../bootstrap.php';

test('Maintenance entity getters and setters', function (): void {
    $maintenance = new Maintenance();

    // test set/get message
    $maintenance->setMessage('Server will be down');
    Assert::equal('Server will be down', $maintenance->message);

    // test set/get type
    $maintenance->setType('warning');
    Assert::equal('warning', $maintenance->getType());
    Assert::equal('prefix-warning', $maintenance->getType('prefix-'));

    // test default type
    $defaultMaintenance = new Maintenance();
    Assert::equal('info', $defaultMaintenance->getType());

    // test set/get expiresAt
    $date = new \DateTimeImmutable('2025-12-31 23:59:59');
    $maintenance->setExpiresAt($date);
    Assert::equal($date, $maintenance->expiresAt);

    // test set null expiresAt
    $maintenance->setExpiresAt(null);
    Assert::null($maintenance->expiresAt);
});
