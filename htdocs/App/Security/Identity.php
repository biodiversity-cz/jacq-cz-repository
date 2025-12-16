<?php declare(strict_types=1);

namespace App\Security;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\User;
use Nette\Security\SimpleIdentity;

class Identity extends SimpleIdentity
{
    private ?int $currentHerbariumId = null;
    private ?string $currentHerbariumAcronym = null;
    private array $herbariums = [];

    public function __construct(User $userEntity)
    {

        $this->herbariums = $userEntity->getHerbaria()->map(fn($h) => $h->id)->toArray();

        $currentHerbariumId = $userEntity->lastVisitedHerbarium?->id;
        $currentHerbariumAcronym = $userEntity->lastVisitedHerbarium?->acronym;

        if ($currentHerbariumId !== null && in_array($currentHerbariumId, $this->herbariums)) {
            $this->currentHerbariumId = $currentHerbariumId;
            $this->currentHerbariumAcronym = $currentHerbariumAcronym;
        }
        // Set the user ID as the identity ID
        parent::__construct($userEntity->id, $this->getUserRoles($userEntity), [
            'name' => $userEntity->getFullname(),
            'email' => $userEntity->email,
            'username' => $userEntity->username
        ]);
    }


    /**
     * Get user roles as an array of role names
     */
    private function getUserRoles(User $userEntity): array
    {
        $roles = [];

        // If there's a current herbarium, get the role for that herbarium
        $currentHerbarium = $userEntity->lastVisitedHerbarium;
        if ($currentHerbarium) {
            $role = $userEntity->getRoleInHerbarium($currentHerbarium);
            if ($role) {
                $roles[] = $role->name;
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

    public function isEligibleForHerbarium(Herbaria $herbarium): bool
    {
        return in_array($herbarium->id, $this->herbariums, true);
    }



}
