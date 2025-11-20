<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\CetafSid;
use App\Model\Database\Entity\Herbaria;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;


class FixtureCetafSid extends FixtureBase
{

    /**
     * Load data fixtures with the passed ObjectManager
     */
    public function load(ObjectManager $manager): void
    {
        $db = new CetafSid()
            ->setStableUri('"http://localhost/cetaf/sid/1"')
            ->setHerbarium($manager->getRepository(Herbaria::class)->findOneBy(['acronym' => 'TEST']))
            ->setCreatedAt()
            ->setLastEditAt();

        $manager->persist($db);
        $manager->flush();
    }


    public function getOrder(): int
    {
        return 20;
    }

}
