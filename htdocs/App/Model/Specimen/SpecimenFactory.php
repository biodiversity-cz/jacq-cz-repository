<?php declare(strict_types = 1);

namespace App\Model\Specimen;

use App\Exceptions\SpecimenIdException;
use App\Services\EntityServices\HerbariumService;
use App\Services\SpecimenIdService;
use Nette\Security\User;

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

        $specimen = new Specimen();
        $specimen->setHerbarium($this->specimenIdService->getHerbariumFromFullId($fullSpecimenId));

        $specimenId = $this->specimenIdService->getInternalPartFromId($fullSpecimenId);
        $specimen->setId($specimenId);

        return $specimen;
    }

    public function createFromInternalPart(User $user, string $specimenId): Specimen
    {
        $specimen = new Specimen();
        $specimen->setHerbarium($this->herbariumService->getCurrentUserHerbarium($user));
        $specimen->setId($specimenId);

        return $specimen;
    }

}
