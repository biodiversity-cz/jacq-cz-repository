<?php declare(strict_types=1);

namespace App\Services;


use App\Exceptions\SpecimenIdException;
use App\Model\Database\Entity\Herbaria;
use App\Services\EntityServices\HerbariumService;

readonly class SpecimenIdService
{

    public const string regexSpecimenPartName = 'specimenId';
    public const string regexHerbariumPartName = 'herbarium';
    public const string regexExtensionPartName = 'extension';

    public function __construct(protected RepositoryConfiguration $repositoryConfiguration, protected HerbariumService $herbariumService)
    {
    }

    public function filenameFitsHerbariumPattern(string $filename, Herbaria $herbarium): bool
    {
        if (preg_match($herbarium->getRegexFilename(), $filename)) {
            return true;
        }
        return false;

    }

    public function getHerbariumFromId(string $specimenId): Herbaria
    {
        $acronym = strtoupper($this->splitSpecimenId($specimenId)[SpecimenIdService::regexHerbariumPartName]);
        $herbarium = $this->herbariumService->findOneWithAcronym($acronym);
        if ($herbarium === null) {
            throw new SpecimenIdException('Unknown herbarium');
        }

        return $herbarium;
    }

    /**
     * @return string[]
     */
    protected function splitSpecimenId(string $specimenId): array
    {
        $parts = [];
        if (preg_match(SpecimenIdService::regexSpecimenPartName, $specimenId, $parts)) {
            return $parts;
        } else {
            throw new SpecimenIdException('invalid name format: ' . $specimenId);
        }
    }

    public function getSpecimenIdFromId(string $specimenId): int
    {
        return (int)$this->splitSpecimenId($specimenId)[SpecimenIdService::regexSpecimenPartName];
    }
}
