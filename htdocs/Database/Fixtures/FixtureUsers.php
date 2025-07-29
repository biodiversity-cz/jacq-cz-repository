<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\User;
use App\Model\Database\Entity\UserRole;
use App\Security\UserAuthenticator;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;


class FixtureUsers extends FixtureBase
{


    /**
     * Load data fixtures with the passed ObjectManager
     */
    public function load(ObjectManager $manager): void
    {
        $herbariumTest = $manager->getRepository(Herbaria::class)->findOneBy(['acronym' => 'TEST']);
        $herbariumPrc = $manager->getRepository(Herbaria::class)->findOneBy(['acronym' => 'PRC']);
        $password = $this->container->getByType(UserAuthenticator::class)->calculateHash('heslo');

        $u1 = new User();
        $u1->setUsername('admin')
            ->setPassword($password)
            ->setName('Petr')
            ->setSurname('Novotný')
            ->setEmail('krkabol@gmail.com')
            ->setHerbarium($herbariumPrc)
            ->setCreatedAt()
            ->setLastEditAt()
            ->setRole($manager->getRepository(UserRole::class)->findOneBy(['name' => 'superadmin']));

        $u2 = new User();
        $u2->setUsername('curator_prc_1')
            ->setPassword($password)
            ->setName('Zdeněk')
            ->setSurname('Vaněček')
            ->setEmail('krkabol@gmail.com')
            ->setHerbarium($herbariumPrc)
            ->setCreatedAt()
            ->setLastEditAt()
            ->setRole($manager->getRepository(UserRole::class)->findOneBy(['name' => 'curator']));

        $u3 = new User();
        $u3->setUsername('curator_test')
            ->setPassword($password)
            ->setName('test')
            ->setSurname('ONLY')
            ->setEmail('krkabol@gmail.com')
            ->setHerbarium($herbariumTest)
            ->setCreatedAt()
            ->setLastEditAt()
            ->setRole($manager->getRepository(UserRole::class)->findOneBy(['name' => 'curator']));

        $manager->persist($u1);
        $manager->persist($u2);
        $manager->persist($u3);
        $manager->flush();
    }


    public function getOrder(): int
    {
        return 3;
    }

}
