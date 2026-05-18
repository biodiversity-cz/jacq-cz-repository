<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\PhotosStatus;
use Tester\Assert;

require_once __DIR__.'/../../../../../bootstrap.php';

test('PhotosStatus entity basic getters/setters and TId trait', function (): void {
    $status = new PhotosStatus();

    // Nastavení a získání jména
    $status->setName('waiting');
    Assert::same('waiting', $status->name);

    // Nastavení a získání popisu
    $status->setDescription('Waiting for processing');
    Assert::same('Waiting for processing', $status->description);

    // Nastavení a získání barvy
    $status->setColor('primary');
    Assert::same('primary', $status->color);

    // Test traitu TId
    $refId = new \ReflectionProperty($status, 'id');

    $refId->setValue($status, 123);
    Assert::same(123, $status->id);

    $clone = clone $status;
    Assert::null($refId->getValue($clone));
});
