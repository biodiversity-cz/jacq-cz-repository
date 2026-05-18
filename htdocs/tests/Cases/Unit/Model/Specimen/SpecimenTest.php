<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Specimen\Specimen;
use Tester\Assert;
use Tests\Toolkit\HerbariumTestFactory;

require_once __DIR__.'/../../../../bootstrap.php';

test('Specimen getters and setters work', function (): void {
    $specimen = new Specimen();
    $herbarium = HerbariumTestFactory::testHerbarium();

    $specimen->setHerbarium($herbarium);
    $specimen->setId('123');

    Assert::same($herbarium, $specimen->herbarium);
    Assert::same('123', $specimen->id);
});

test('Specimen creates standardized ID correctly', function (): void {
    $specimen = new Specimen();

    $herbarium = HerbariumTestFactory::testHerbarium();

    $specimen->setHerbarium($herbarium);
    $specimen->setId('42');

    $expected = 'TEST-'.sprintf('%0'.$herbarium->digitsCount.'d', 42);
    Assert::same($expected, $specimen->getStandardizedId());
});
