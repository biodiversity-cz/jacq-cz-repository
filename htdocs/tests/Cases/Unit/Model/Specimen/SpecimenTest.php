<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Bootstrap;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\IIIF\ManifestFactory;
use App\Model\Specimen\Specimen;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\LinkGenerator;
use Tester\Assert;
use Tests\Toolkit\HerbariumTestFactory;

require_once __DIR__ . '/../../../../bootstrap.php';


test('Specimen getters and setters work', function (): void {
    $specimen = new Specimen();
    $herbarium = HerbariumTestFactory::testHerbarium();

    $specimen->setHerbarium($herbarium);
    $specimen->setId("123");

    Assert::same($herbarium, $specimen->herbarium);
    Assert::same(123, $specimen->id);
});

test('Specimen creates standardized ID correctly', function (): void {
    $specimen = new Specimen();

    $herbarium = HerbariumTestFactory::testHerbarium();

    $specimen->setHerbarium($herbarium);
    $specimen->setId("42");

    $expected = 'TEST-' . sprintf('%0'.$this->herbarium->digitsCount.'d', 42);
    Assert::same($expected, $specimen->getStandardizedId());
});
