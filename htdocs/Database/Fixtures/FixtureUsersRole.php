<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\UserRole;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;


class FixtureUsersRole extends FixtureBase
{

    /**
     * Load data fixtures with the passed ObjectManager
     */
    public function load(ObjectManager $manager): void
    {
        $r1 = new UserRole()
            ->setName('superadmin')
            ->setDescription('all privileges');

        $r2 = new UserRole()
            ->setName('admin')
            ->setDescription('curator privileges over all herbaria');

        $r3 = new UserRole()
            ->setName('curator')
            ->setDescription('manage photos in single herbarium');

        $r4 = new UserRole()
            ->setName('guest')
            ->setDescription('read only access to single herbarium');

        $manager->persist($r1);
        $manager->persist($r2);
        $manager->persist($r3);
        $manager->persist($r4);
        $manager->flush();
    }


    public function getOrder(): int
    {
        return 20;
    }

}
