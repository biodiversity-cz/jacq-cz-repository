<?php declare(strict_types=1);

namespace Tests\Toolkit;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\License;
use App\Model\Database\Entity\Photos;

final class PhotoTestFactory
{
    public static function minimal(): Photos
    {
        $photo = new Photos();

        // --- Photo ---
        self::set($photo, 'id', 1);
        self::set($photo, 'specimenId', '5');

        self::set($photo, 'width', null);
        self::set($photo, 'height', null);
        self::set($photo, 'originalFilename', 'specimen_123.tif');
        self::set($photo, 'archiveFilename', null);
        self::set($photo, 'jp2Filename', null);

        self::set($photo, 'createdAt', new \DateTimeImmutable('2023-01-01 00:00:00'));
        self::set($photo, 'lastEdit', new \DateTime('2023-01-16 14:20:00'));

        $herbarium = HerbariumTestFactory::testHerbarium();

        self::set($photo, 'herbarium', $herbarium);

        return $photo;
    }

    public static function detailed(): Photos
    {
        $photo = new Photos();

        // --- Photo ---
        self::set($photo, 'id', 1);
        self::set($photo, 'specimenId', '5');
        self::set($photo, 'width', 3000);
        self::set($photo, 'height', 2000);
        self::set($photo, 'originalFilename', 'specimen_123.tif');
        self::set($photo, 'archiveFilename', 'PRC_000123_789.tif');
        self::set($photo, 'jp2Filename', 'test.jp2');

        self::set($photo, 'createdAt', new \DateTimeImmutable('2023-01-15 10:30:00'));
        self::set($photo, 'lastEdit', new \DateTime('2023-01-16 14:20:00'));

        $herbarium = HerbariumTestFactory::testHerbarium();
        self::set($photo, 'herbarium', $herbarium);

        return $photo;
    }

    private static function set(object $o, string $p, mixed $v): void
    {
        $r = new \ReflectionProperty($o, $p);
        $r->setValue($o, $v);
    }
}
