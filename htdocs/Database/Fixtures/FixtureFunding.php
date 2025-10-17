<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\Funding;
use App\Model\Database\Entity\Herbaria;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;


class FixtureFunding extends FixtureBase
{

    public function load(ObjectManager $manager): void
    {

        $s1 = new Funding();
        $s1->setName('Grant 1')
            ->setDescription('obecný grant do kterého mohou přispívat všichni')
            ->setCode('123.456.789')
            ->setFunder('funded by funder')
            ->setNote('internal note')
            ->setHerbarium(null)
            ->setActive(true)
            ->setCreatedAt()
            ->setLastEditAt();

        $s3 = new Funding();
        $s3->setName('Grant nonactive')
            ->setDescription('obecný grant do kterého mohou přispívat všichni')
            ->setCode('56.789')
            ->setFunder('funded by funder')
            ->setNote('internal note')
            ->setHerbarium(null)
            ->setActive(false)
            ->setCreatedAt()
            ->setLastEditAt();

        $herbariumTest =  $manager->getRepository(Herbaria::class)->find(1);
        $s2 = new Funding();
        $s2->setName('Grant 2 private')
            ->setDescription('TEST only avaialable')
            ->setHerbarium($herbariumTest)
            ->setActive(true)
            ->setCreatedAt()
            ->setLastEditAt();

        $manager->persist($s1);
        $manager->persist($s2);
        $manager->persist($s3);
        $manager->flush();
    }


    public function getOrder(): int
    {
        return 80;
    }

}
