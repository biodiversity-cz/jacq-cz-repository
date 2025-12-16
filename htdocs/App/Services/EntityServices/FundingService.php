<?php declare(strict_types=1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\Funding;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

class FundingService extends BaseEntityService
{
    protected string $entityName = Funding::class;

    public function update(User $user, Funding $funding): Funding
    {
        if ($user->getIdentity()->getCurrentHerbariumId() !== $funding->herbarium->id || $user->isInRole('ROLE_ADMIN')) {
            throw new AuthenticationException();
        }
        $this->entityManager->persist($funding);
        $this->entityManager->flush();
        return $funding;
    }

    public function create(Funding $funding): Funding
    {
        $this->entityManager->persist($funding);
        $this->entityManager->flush();
        return $funding;
    }

    public function delete(User $user, Funding $funding): self
    {
        if ($user->getIdentity()->getCurrentHerbariumId() !== $funding->herbarium->id || $user->isInRole('ROLE_ADMIN')) {
            throw new AuthenticationException();
        }
        $this->entityManager->remove($funding);
        $this->entityManager->flush();
        return $this;
    }

}
