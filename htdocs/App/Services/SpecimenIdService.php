<?php declare(strict_types=1);

namespace App\Services;

use App\Exceptions\SpecimenIdException;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Services\EntityServices\HerbariumService;

class SpecimenIdService
{

    public const string REGEX_SPECIMEN = 'specimenId';
    public const string REGEX_HERBARIUM = 'herbarium';
    public const string REGEX_EXTENSION = 'extension';

    public const string REGEX_PUBLIC_SPECIMEN_ID = '/^(?<' . self::REGEX_HERBARIUM . '>[a-zA-Z]+)[\s\-–_](?<' . self::REGEX_SPECIMEN . '>\d+)$/iu';

    public function __construct(protected RepositoryConfiguration $repositoryConfiguration, protected HerbariumService $herbariumService)
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

    public function generateArk(Photos $photo): string
    {
        $settings = $this->repositoryConfiguration->getArkProperties();
        // ark:12661/nrp1HERB/PRC/PRC_37/321354321
        $ark =
            'ark:' . $settings['naan'] . "/" .
            $settings['shoulder'].
            $settings['repository']. "/" .
            $photo->herbarium->acronym . "/" .
            $photo->getFullSpecimenId() . "/".
            $photo->id;
        return $ark;
    }

}
