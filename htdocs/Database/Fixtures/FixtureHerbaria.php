<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\ExternalDatabase;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\License;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;

class FixtureHerbaria extends FixtureBase
{
    /**
     * Load data fixtures with the passed ObjectManager.
     */
    public function load(ObjectManager $manager): void
    {
        $license = $manager->getRepository(License::class)->findOneBy(['default' => true]);
        $externalDb = $manager->getRepository(ExternalDatabase::class)->find(2);
        $herbariumTest = new Herbaria()
            ->setAcronym('TEST')
            ->setBucket('herbarium-test')
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false)
            ->setLicense($license)
            ->setDigitsCount(6)
            ->setRegexBarcode('/^(?<herbarium>test)[\s\-–_](?<specimenId>\d+)$/i')
            ->setRegexFilename('/^(?<herbarium>test)_(?<specimenId>\d+)(?<supplement>[_\-a-z]*)\.(?<extension>tif)$/i')
            ->setExternalDatabase($externalDb);

        $herbariumPrc = new Herbaria()
            ->setAcronym('PRC')
            ->setBucket('herbarium-prc')
            ->setFullname('PřF UK Praha')
            ->setLicense($license)
            ->setDigitsCount(6)
            ->setAddress('Benátská 2, Prague')
            ->setLogo('https://cas.cuni.cz/cas/images/UK-logo.png')
            ->setFallbackFilename(false)
            ->setMultipleBarcodeMultiplier(false)
            ->setRegexBarcode('/^(?<herbarium>prc)[\s\-–_](?<specimenId>\d+)$/i')
            ->setRegexFilename('/^(?<herbarium>prc)_(?<specimenId>\d+)(?<supplement>[_\-a-z]*)\.(?<extension>tif)$/i')
            ->setExternalDatabase($externalDb);

        $manager->persist($herbariumTest);
        $manager->persist($herbariumPrc);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 10;
    }
}
