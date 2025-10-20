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
            ->setCcmmFormat('<funding_reference>
        <iri>https://funder-org.org/grants/123456789</iri>
        <local_identifier>https://doi.org/award-identifier</local_identifier>
        <award_title>Program for air pollution research</award_title>
        <funding_program>https://funder-org.org/program/abcdefgh</funding_program>
        <funder>
            <organization>
                <iri>https://ror.org/01pv73b02</iri>
                <identifier>
                    <value>01pv73b02</value>
                    <scheme>
                        <iri>https://ror.org/</iri>
                        <label xml:lang="">ROR</label>
                    </scheme>
                </identifier>
                <name>Grantová agentura České republiky</name>
            </organization>
        </funder>
    </funding_reference>')
            ->setCreatedAt()
            ->setLastEditAt();

        $s2 = new Funding();
        $s2->setName('Grant nonactive')
            ->setDescription('obecný grant do kterého mohou přispívat všichni')
            ->setCode('56.789')
            ->setFunder('funded by funder')
            ->setNote('internal note')
            ->setHerbarium(null)
            ->setActive(false)
            ->setCreatedAt()
            ->setLastEditAt();

        $herbariumTest =  $manager->getRepository(Herbaria::class)->findOneBy(['acronym'=>'PRC']);
        $s3 = new Funding();
        $s3->setName('Grant 2 private')
            ->setDescription('PRC only avaialable')
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
