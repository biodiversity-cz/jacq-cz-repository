<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'user_herbarium_role', options: ['comment' => 'User roles in specific herbaria'])]
class UserHerbariumRole
{
    use TId;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'userHerbariumRoles')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    public protected(set) User $user;

    #[ManyToOne(targetEntity: Herbaria::class, inversedBy: 'userHerbariumRoles')]
    #[JoinColumn(name: 'herbarium_id', referencedColumnName: 'id', nullable: false)]
    public protected(set) Herbaria $herbarium;

    #[ManyToOne(targetEntity: UserRole::class)]
    #[JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false)]
    public protected(set) UserRole $role;

    public function setUser(User $user): UserHerbariumRole
    {
        $this->user = $user;

        return $this;
    }

    public function setHerbarium(Herbaria $herbarium): UserHerbariumRole
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function setRole(UserRole $role): UserHerbariumRole
    {
        $this->role = $role;

        return $this;
    }
}
