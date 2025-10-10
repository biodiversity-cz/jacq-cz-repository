<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\ExternalDatabase;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;


class FixtureExternalDatabase extends FixtureBase
{

    /**
     * Load data fixtures with the passed ObjectManager
     */
    public function load(ObjectManager $manager): void
    {
        $db = new ExternalDatabase()
            ->setName('jacq.org')
            ->setUrl('https://api.jacq.org/v1/stableIdentifier/resolve/')
            ->setElement('specimenID')
            ->setDescription('default external database');
//        $manager->persist($db);

        $manager->flush();
    }


    public function getOrder(): int
    {
        return 2;
    }

}
