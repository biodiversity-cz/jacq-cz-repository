<?php declare(strict_types=1);

namespace Tests\Toolkit;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\License;
use App\Model\Database\Entity\Photos;
use App\Model\Specimen\Specimen;

final class SpecimenTestFactory
{
    public static function minimal(): Specimen
    {
        $specimen = new Specimen();
        $specimen->setHerbarium(HerbariumTestFactory::testHerbarium());
        self::set($specimen, 'numericPartOfId', 123);
        return $specimen;
    }

    private static function set(object $o, string $p, mixed $v): void
    {
        $r = new \ReflectionProperty($o, $p);
        $r->setValue($o, $v);
    }
}
