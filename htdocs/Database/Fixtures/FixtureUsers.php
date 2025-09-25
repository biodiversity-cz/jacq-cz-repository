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
        
        $superadminRole = $manager->getRepository(UserRole::class)->findOneBy(['name' => 'superadmin']);
        $curatorRole = $manager->getRepository(UserRole::class)->findOneBy(['name' => 'curator']);

        $u1 = new User();
        $u1->setUsername('admin')
            ->setPassword($password)
            ->setName('Petr')
            ->setSurname('Novotný')
            ->setEmail('krkabol@gmail.com')
            ->setLastVisitedHerbarium($herbariumPrc)
            ->setCreatedAt()
            ->setLastEditAt();
            
        // Assign role to herbarium
        $u1->assignRoleToHerbarium($herbariumPrc, $superadminRole);

        $u2 = new User();
        $u2->setUsername('curator_prc_1')
            ->setPassword($password)
            ->setName('Zdeněk')
            ->setSurname('Vaněček')
            ->setEmail('krkabol@gmail.com')
            ->setLastVisitedHerbarium($herbariumPrc)
            ->setCreatedAt()
            ->setLastEditAt();
            
        // Assign role to herbarium
        $u2->assignRoleToHerbarium($herbariumPrc, $curatorRole);

        $u3 = new User();
        $u3->setUsername('curator_test')
            ->setPassword($password)
            ->setName('test')
            ->setSurname('ONLY')
            ->setEmail('krkabol@gmail.com')
            ->setLastVisitedHerbarium($herbariumTest)
            ->setCreatedAt()
            ->setLastEditAt();
            
        // Assign role to herbarium
        $u3->assignRoleToHerbarium($herbariumTest, $curatorRole);

        $manager->persist($u1);
        $manager->persist($u2);
        $manager->persist($u3);
        $manager->flush();
    }


    public function getOrder(): int
    {
        return 30;
    }

}
