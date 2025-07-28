<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nette\DI\Container;
use Nettrine\Fixtures\Fixture\ContainerAwareInterface;


class Fixture20250728 implements FixtureInterface, OrderedFixtureInterface, ContainerAwareInterface
{

    private Container $container;

    public function setContainer(Container $container): void
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed ObjectManager
     */
    public function load(ObjectManager $manager): void
    {
        $herbariumTest = new Herbaria()
        ->setAcronym('TEST')
        ->setRegexBarcode('TESTXXX');
        $manager->persist($herbariumTest);

        $userPR = new User();
        $userPR->setPassword('heslo')
        ->setName('only')
        ->setSurname('TEST')
        ->setHerbarium($herbariumTest);

        $manager->persist($userPR);
        $manager->flush();
    }


    public function getOrder(): int
    {
        return 20250728;
    }

}
