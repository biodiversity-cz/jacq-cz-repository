<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\Contact;
use App\Model\Database\Entity\Herbaria;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;

class FixtureContact extends FixtureBase
{
    /**
     * Load data fixtures with the passed ObjectManager.
     */
    public function load(ObjectManager $manager): void
    {
        $c1 = new Contact();
        $c1->setName('Patrik')
            ->setSurname('Mráz')
            ->setEmail('mrazpat@natur.cuni.cz')
            ->setDescription('head of herbarium')
            ->setHerbarium($manager->getRepository(Herbaria::class)->findOneBy(['acronym' => 'PRC']));

        $manager->persist($c1);
        $manager->flush();
    }

    public function getOrder(): int
    {
        return 50;
    }
}
