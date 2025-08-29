<?php

declare(strict_types=1);

namespace Database\Fixtures;

use App\Model\Database\Entity\PhotosStatus;
use Database\Base\FixtureBase;
use Doctrine\Persistence\ObjectManager;


class FixturePhotosStatus extends FixtureBase
{

    /**
     * Load data fixtures with the passed ObjectManager
     */
    public function load(ObjectManager $manager): void
    {

        $s1 = new PhotosStatus();
        $s1->setName('waiting before control')
            ->setColor('warning')
            ->setDescription('Photo to be copied from the users bucket, do not delete manually!');

        $s2 = new PhotosStatus();
        $s2->setName('control error')
            ->setColor('danger')
            ->setDescription('Entry control did not passed, it is not possible to include this photo in the repository');

        $s3 = new PhotosStatus();
        $s3->setName('control ok')
            ->setColor('primary')
            ->setDescription('Entry control passed well, it is time to include it');

        $s4 = new PhotosStatus();
        $s4->setName('published')
            ->setColor('success')
            ->setDescription('Photo is stored in the repository - final status for most photo');

        $s5 = new PhotosStatus();
        $s5->setName('hidden')
            ->setColor('secondary')
            ->setDescription('Photo is stored in the repository (=published) but public should not see it - contains error or is not devoted for public');

        $manager->persist($s1);
        $manager->persist($s2);
        $manager->persist($s3);
        $manager->persist($s4);
        $manager->persist($s5);
        $manager->flush();
    }


    public function getOrder(): int
    {
        return 40;
    }

}
