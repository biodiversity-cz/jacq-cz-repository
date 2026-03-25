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
            ->setUrl('https://api.jacq.org/v1/objects/specimens/by-sid/')
            ->setElement('specimenID')
            ->setDescription('default external database');
        $manager->persist($db);

        $dbInternal = new ExternalDatabase()
            ->setName('biodiversity.cz')
            ->setUrl('http://nginx:8080/cetaf/exists/')
            ->setElement('specimenID')
            ->setDescription('internal resolver');
        $manager->persist($dbInternal);

        $manager->flush();
    }


    public function getOrder(): int
    {
        return 2;
    }

}
