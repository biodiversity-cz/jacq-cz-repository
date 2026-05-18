<?php

declare(strict_types=1);

namespace Tests\Toolkit;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\License;

final class HerbariumTestFactory
{
    public static function testHerbarium(): Herbaria
    {
        // --- License ---
        $license = new License();
        self::set($license, 'link', 'https://licence.org');

        // --- Herbarium ---
        $herbarium = new Herbaria();
        self::set($herbarium, 'acronym', 'TEST');
        self::set($herbarium, 'fullname', 'testing herbarium');
        self::set($herbarium, 'address', 'nowhere');
        self::set($herbarium, 'license', $license);

        return $herbarium;
    }

    private static function set(object $o, string $p, mixed $v): void
    {
        $r = new \ReflectionProperty($o, $p);
        $r->setValue($o, $v);
    }
}
