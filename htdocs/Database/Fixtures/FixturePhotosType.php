<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\PhotosType;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;


class FixturePhotosType extends FixtureBase
{

    /**
     * Load data fixtures with the passed ObjectManager
     */
    public function load(ObjectManager $manager): void
    {

        $t1 = new PhotosType();
        $t1->setName('preserved specimen')
            ->setDescription('Scan or photo of preserved herbarium specimen')
            ->setColor('primary');

        $t2 = new PhotosType();
        $t2->setName('field')
            ->setDescription('A photo from the field of the plant from which the sample was taken for the herbarium specimen')
            ->setColor('primary');

        $t3 = new PhotosType();
        $t3->setName('microscopy')
            ->setDescription('Microscopic image')
            ->setColor('primary');

        $t4 = new PhotosType();
        $t4->setName('illustration')
            ->setDescription('Illustration/drawing of the speimen parts')
            ->setColor('primary');

        $manager->persist($t1);
        $manager->persist($t2);
        $manager->persist($t3);
        $manager->persist($t4);
        $manager->flush();
    }


    public function getOrder(): int
    {
        return 60;
    }

}
