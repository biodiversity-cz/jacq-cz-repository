<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\Herbaria;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;


class FixtureHerbaria extends FixtureBase
{

    /**
     * Load data fixtures with the passed ObjectManager
     */
    public function load(ObjectManager $manager): void
    {
        $herbariumTest = new Herbaria()
            ->setAcronym('TEST')
            ->setBucket('herbarium-test')
            ->setFallbackFilename(true)
            ->setRegexBarcode('/^(?<herbarium>test)[\s\-–_](?<specimenId>\d+)$/i')
            ->setRegexFilename('/^(?<herbarium>test)_(?<specimenId>\d+)(?<supplement>[_\-a-z]*)\.(?<extension>tif)$/i');

        $herbariumPrc = new Herbaria()
            ->setAcronym('PRC')
            ->setBucket('herbarium-prc')
            ->setFullname('PřF UK Praha')
            ->setAddress('Benátská 2, Prague')
            ->setLogo('https://cas.cuni.cz/cas/images/UK-logo.png')
            ->setFallbackFilename(false)
            ->setRegexBarcode('/^(?<herbarium>prc)[\s\-–_](?<specimenId>\d+)$/i')
            ->setRegexFilename('/^(?<herbarium>prc)_(?<specimenId>\d+)(?<supplement>[_\-a-z]*)\.(?<extension>tif)$/i');

        $manager->persist($herbariumTest);
        $manager->persist($herbariumPrc);

        $manager->flush();
    }


    public function getOrder(): int
    {
        return 1;
    }

}
