<?php declare(strict_types=1);

namespace App\Services;

use App\Exceptions\SpecimenIdException;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Specimen\SpecimenFactory;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;

class SpecimenIdService
{

    public const string REGEX_SPECIMEN = 'specimenId';
    public const string REGEX_HERBARIUM = 'herbarium';
    public const string REGEX_EXTENSION = 'extension';

    public const string REGEX_PUBLIC_SPECIMEN_ID = '/^(?<' . self::REGEX_HERBARIUM . '>[a-zA-Z]+)[\s\-–_](?<' . self::REGEX_SPECIMEN . '>\d+)$/iu';

    public function __construct(protected RepositoryConfiguration $repositoryConfiguration, protected HerbariumService $herbariumService, protected SpecimenFactory $specimenFactory, protected PhotoService $photoService)
    {
    }

    public function getHerbariumFromId(string $specimenId): Herbaria
    {
        $acronym = strtoupper($this->splitSpecimenId($specimenId)[self::REGEX_HERBARIUM]);
        $herbarium = $this->herbariumService->findOneWithAcronym($acronym);
        if ($herbarium === null) {
            throw new SpecimenIdException('Unknown herbarium');
        }

        return $herbarium;
    }

    public function getNumericPartFromId(string $specimenId): int
    {
        return (int)$this->splitSpecimenId($specimenId)[self::REGEX_SPECIMEN];
    }

    /**
     * @return string[]
     */
    protected function splitSpecimenId(string $specimenId): array
    {
        $parts = [];
        if (preg_match(self::REGEX_PUBLIC_SPECIMEN_ID, $specimenId, $parts)) {
            return $parts;
        } else {
            throw new SpecimenIdException('invalid name format: ' . $specimenId);
        }
    }

    /**
     * responsible for ARK PID identifier generation.
     * the PID is stored in db, but the hierarchical nature of ARK requires to be able to resolve individual subpaths --> do not change unless really sure about
     * template = ark:12661/nrp1HERB/PRC/37/321354321
     *
     * synergic with \App\UI\Front\Ark\ArkPresenter
     */
    public function generateArk(Photos $photo): string
    {
        $settings = $this->repositoryConfiguration->getArkProperties();

        $ark =
            'ark:' . $settings['naan'] . "/" .
            $settings['shoulder'].
            $settings['repository']. "/" .
            $photo->herbarium->acronym . "/" .
            $photo->specimenId . "/".
            $photo->id;
        return $ark;
    }

    public function getSpecimenPid(Photos $photo): string
    {
        if ($photo->status->id === PhotosStatus::PUBLISHED){
            return substr($photo->pid, 0, strrpos($photo->pid, '/'));
        }
        $specimen = $this->specimenFactory->create($photo->getFullSpecimenId());
        $publicPhotos = $this->photoService->getPublicPhotosOfSpecimen($specimen);
        if (!empty($publicPhotos)) {
            return substr($publicPhotos[0]->pid, 0, strrpos($publicPhotos[0]->pid, '/'));
        }
        return '';
    }

}
