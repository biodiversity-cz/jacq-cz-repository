<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\Herbaria;
use Nette\Security\User;

class HerbariumService extends BaseEntityService
{

    protected string $entityName = Herbaria::class;

    public function getCurrentUserHerbarium(User $user): Herbaria
    {
        return $this->entityManager->getReference($this->entityName, $user->getIdentity()->herbarium);
    }

    public function findOneWithAcronym(string $acronym): ?Herbaria
    {
        return $this->repository->findOneWithAcronym($acronym);
    }

    public function setFilenameFallback(User $user, bool $value): HerbariumService
    {
        $herbarium = $this->getCurrentUserHerbarium($user);
        $herbarium->setFallbackFilename($value);
        $this->entityManager->flush();
        return $this;
    }

    public function setMultiplier(User $user, bool $value): HerbariumService
    {
        $herbarium = $this->getCurrentUserHerbarium($user);
        $herbarium->setMultipleBarcodeMultiplier($value);
        $this->entityManager->flush();
        return $this;
    }
}
