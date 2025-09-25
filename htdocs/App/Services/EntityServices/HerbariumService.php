<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\Herbaria;
use App\Security\Identity;
use Nette\Security\User;

class HerbariumService extends BaseEntityService
{

    protected string $entityName = Herbaria::class;

    public function getCurrentUserHerbarium(User $user): ?Herbaria
    {
        $identity = $user->getIdentity();

        $lastVisitedHerbariumId = $identity->getCurrentHerbariumId();
        if ($lastVisitedHerbariumId) {
            return $this->entityManager->getReference($this->entityName, $lastVisitedHerbariumId);
        }

        // If no last visited herbarium, return the first one from the user's herbariums
        $herbariums = $identity->herbariums;
        if (!empty($herbariums)) {
            return $this->entityManager->getReference($this->entityName, $herbariums[0]);
        }

        return null;
    }

    public function findOneWithAcronym(string $acronym): ?Herbaria
    {
        return $this->repository->findOneWithAcronym($acronym);
    }

    public function setFilenameFallback(User $user, bool $value): HerbariumService
    {
        $herbarium = $this->getCurrentUserHerbarium($user);
        if ($herbarium) {
            $herbarium->setFallbackFilename($value);
            $this->entityManager->flush();
        }
        return $this;
    }

    public function setMultiplier(User $user, bool $value): HerbariumService
    {
        $herbarium = $this->getCurrentUserHerbarium($user);
        if ($herbarium) {
            $herbarium->setMultipleBarcodeMultiplier($value);
            $this->entityManager->flush();
        }
        return $this;
    }
}
