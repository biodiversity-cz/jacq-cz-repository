<?php

declare(strict_types=1);

namespace App\Model\Specimen;

use App\Exceptions\SpecimenIdException;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\PhotoService;
use App\Services\SpecimenIdService;
use Nette\Security\User;

class SpecimenFactory
{
    public function __construct(protected readonly HerbariumService $herbariumService, protected readonly SpecimenIdService $specimenIdService, protected readonly PhotoService $photoService)
    {
    }

    public function create(string $fullSpecimenId): Specimen
    {
        if ('' === $fullSpecimenId) {
            throw new SpecimenIdException('Specimen id cannot be empty');
        }

        $specimen = new Specimen();
        $specimen->setHerbarium($this->specimenIdService->getHerbariumFromFullId($fullSpecimenId));

        $specimenId = $this->specimenIdService->getInternalPartFromId($fullSpecimenId);
        $specimen->setId($specimenId);

        return $specimen;
    }

    public function createFromSid(string $specimenSid): Specimen
    {
        if ('' === $specimenSid) {
            throw new SpecimenIdException('Specimen id cannot be empty');
        }

        $publicPhoto = $this->photoService->getPublicPhotoBySpecimenSid($specimenSid);
        if (null === $publicPhoto) {
            throw new SpecimenIdException('Specimen not found');
        }
        $specimen = new Specimen();

        $specimen->setHerbarium($publicPhoto->herbarium);
        $specimen->setId($publicPhoto->specimenId);

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
