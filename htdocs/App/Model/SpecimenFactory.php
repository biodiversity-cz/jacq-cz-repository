<?php declare(strict_types = 1);

namespace App\Model;

use App\Exceptions\SpecimenIdException;
use App\Services\EntityServices\HerbariumService;
use App\Services\SpecimenIdService;

class SpecimenFactory
{

    public function __construct(protected readonly HerbariumService $herbariumService, protected readonly SpecimenIdService $specimenIdService)
    {
    }

    public function create(string $fullSpecimenId): Specimen
    {
        if ($fullSpecimenId === '') {
            throw new SpecimenIdException('Specimen id cannot be empty');
        }

        $specimen = new Specimen($fullSpecimenId);
        $specimen->setHerbarium($this->specimenIdService->getHerbariumFromId($fullSpecimenId));

        $specimenId = $this->specimenIdService->getSpecimenIdFromId($fullSpecimenId);
        $specimen->setSpecimenId($specimenId);

        return $specimen;
    }

}
