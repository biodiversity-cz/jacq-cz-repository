<?php declare(strict_types=1);

namespace App\Security;

use App\Model\Database\Entity\User;
use Nette\Security\SimpleIdentity;

class Identity extends SimpleIdentity
{
    private ?int $currentHerbariumId = null;
    private ?string $currentHerbariumAcronym = null;
    private array $herbariums = [];

    public function __construct(User $userEntity)
    {

        $this->herbariums = $userEntity->getHerbaria()->map(fn($h) => $h->getId())->toArray();

        $currentHerbariumId = $userEntity->getLastVisitedHerbarium()?->getId();
        $currentHerbariumAcronym = $userEntity->getLastVisitedHerbarium()?->getAcronym();

        if ($currentHerbariumId !== null && in_array($currentHerbariumId, $this->herbariums)) {
            $this->currentHerbariumId = $currentHerbariumId;
            $this->currentHerbariumAcronym = $currentHerbariumAcronym;
        }
        // Set the user ID as the identity ID
        parent::__construct($userEntity->getId(), $this->getUserRoles($userEntity), [
            'name' => $userEntity->getFullname(),
            'email' => $userEntity->getEmail(),
            'username' => $userEntity->getUsername()
        ]);
    }


    /**
     * Get user roles as an array of role names
     */
    private function getUserRoles(User $userEntity): array
    {
        $roles = [];

        // If there's a current herbarium, get the role for that herbarium
        $currentHerbarium = $userEntity->getLastVisitedHerbarium();
        if ($currentHerbarium) {
            $role = $userEntity->getRoleInHerbarium($currentHerbarium);
            if ($role) {
                $roles[] = $role->getName();
            }
        }

        // Also add any global roles if they exist
        // For now, we'll just return the role for the current herbarium
        return $roles;
    }

    /**
     * Get the current herbarium
     */
    public function getCurrentHerbariumId(): ?int
    {
        return $this->currentHerbariumId;
    }

    public function getCurrentHerbariumAcronym(): ?string
    {
        return $this->currentHerbariumAcronym;
    }



}
