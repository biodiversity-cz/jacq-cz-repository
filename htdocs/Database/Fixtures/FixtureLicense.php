<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\License;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;


class FixtureLicense extends FixtureBase
{

    /**
     * Load data fixtures with the passed ObjectManager
     */
    public function load(ObjectManager $manager): void
    {
        $license = new License()
            ->setAcronym('CC-BY')
            ->setLink('https://creativecommons.org/licenses/by/4.0/')
            ->setDefault(true);
        $manager->persist($license);

        $manager->flush();
    }


    public function getOrder(): int
    {
        return 1;
    }

}
