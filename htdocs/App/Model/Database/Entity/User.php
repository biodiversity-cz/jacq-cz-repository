<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'users', options: ['comment' => 'Repository users'])]
class User
{

    use TId;
    use TCreatedAt;
    use TLastEditAt;

    #[Column(unique: true, nullable: false)]
    protected string $username;

    #[Column(nullable: false)]
    protected string $password;

    #[Column(nullable: false)]
    protected string $name;

    #[Column(nullable: false)]
    protected string $surname;

    #[Column(nullable: false, options: ['comment' => 'User email address'])]
    protected string $email;

    #[Column(type: Types::TEXT, length: 5000, nullable: true, options: ['comment' => 'OpenID subject identifier'])]
    protected ?string $openidSubject = null;

    #[Column(type: Types::TEXT, length: 5000, nullable: true, options: ['comment' => 'OpenID provider'])]
    protected ?string $openidProvider = null;

    #[Column(type: Types::TEXT, length: 5000, nullable: true, options: ['comment' => 'OpenID ID token'])]
    protected ?string $openidIdToken = null;

    #[Column(type: Types::TEXT, length: 5000, nullable: true, options: ['comment' => 'OpenID refresh token'])]
    protected ?string $openidRefreshToken = null;

    #[ManyToOne(targetEntity: Herbaria::class)]
    #[JoinColumn(name: 'last_visited_herbarium',   referencedColumnName: 'id', nullable: true, options: ['comment' => 'Last visited herbarium'])]
    protected ?Herbaria $lastVisitedHerbarium = null;

    #[Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => 'Option to disable access for a specific user'])]
    protected bool $active = true;

    #[Column(type: Types::TEXT, length: 60000, nullable: true, options: ['comment' => 'additional information about user'])]
    protected ?string $comment;

    #[OneToMany(targetEntity: UserHerbariumRole::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected Collection $userHerbariumRoles;

    public function __construct()
    {
        $this->userHerbariumRoles = new ArrayCollection();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): User
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;

        return $this;
    }


    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): User
    {
        $this->active = $active;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): User
    {
        $this->comment = $comment;

        return $this;
    }

    public function getFullname(): string
    {
        return $this->getName() . ' ' . $this->getSurname();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): User
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): User
    {
        $this->surname = $surname;

        return $this;
    }

    public function getOpenidSubject(): ?string
    {
        return $this->openidSubject;
    }

    public function setOpenidSubject(?string $openidSubject): User
    {
        $this->openidSubject = $openidSubject;
        return $this;
    }

    public function getOpenidProvider(): ?string
    {
        return $this->openidProvider;
    }

    public function setOpenidProvider(?string $openidProvider): User
    {
        $this->openidProvider = $openidProvider;
        return $this;
    }

    public function getOpenidIdToken(): ?string
    {
        return $this->openidIdToken;
    }

    public function setOpenidIdToken(?string $openidIdToken): User
    {
        $this->openidIdToken = $openidIdToken;
        return $this;
    }

    public function getOpenidRefreshToken(): ?string
    {
        return $this->openidRefreshToken;
    }

    public function setOpenidRefreshToken(?string $openidRefreshToken): User
    {
        $this->openidRefreshToken = $openidRefreshToken;
        return $this;
    }

    public function getUserHerbariumRoles(): Collection
    {
        return $this->userHerbariumRoles;
    }

    public function addUserHerbariumRole(UserHerbariumRole $userHerbariumRole): User
    {
        if (!$this->userHerbariumRoles->contains($userHerbariumRole)) {
            $this->userHerbariumRoles->add($userHerbariumRole);
            $userHerbariumRole->setUser($this);
        }

        return $this;
    }

    public function removeUserHerbariumRole(UserHerbariumRole $userHerbariumRole): User
    {
        $this->userHerbariumRoles->removeElement($userHerbariumRole);

        return $this;
    }

    /**
     * Get all herbaria this user has access to
     */
    public function getHerbaria(): Collection
    {
        return $this->userHerbariumRoles->map(fn(UserHerbariumRole $uhr) => $uhr->getHerbarium());
    }

    /**
     * Get the role of this user in a specific herbarium
     */
    public function getRoleInHerbarium(Herbaria $herbarium): ?UserRole
    {
        foreach ($this->userHerbariumRoles as $userHerbariumRole) {
            if ($userHerbariumRole->getHerbarium()->getId() === $herbarium->getId()) {
                return $userHerbariumRole->getRole();
            }
        }

        return null;
    }

    /**
     * Check if user has a specific role in a herbarium
     */
    public function hasRoleInHerbarium(Herbaria $herbarium, string $roleName): bool
    {
        $role = $this->getRoleInHerbarium($herbarium);
        return $role && $role->getName() === $roleName;
    }

    /**
     * Assign a role to this user in a specific herbarium
     */
    public function assignRoleToHerbarium(Herbaria $herbarium, UserRole $role): User
    {
        // Check if user already has a role in this herbarium
        foreach ($this->userHerbariumRoles as $userHerbariumRole) {
            if ($userHerbariumRole->getHerbarium()->getId() === $herbarium->getId()) {
                $userHerbariumRole->setRole($role);
                return $this;
            }
        }

        // Create new UserHerbariumRole
        $userHerbariumRole = new UserHerbariumRole();
        $userHerbariumRole->setUser($this);
        $userHerbariumRole->setHerbarium($herbarium);
        $userHerbariumRole->setRole($role);

        $this->userHerbariumRoles->add($userHerbariumRole);

        return $this;
    }

    public function getLastVisitedHerbarium(): ?Herbaria
    {
        return $this->lastVisitedHerbarium;
    }

    public function setLastVisitedHerbarium(?Herbaria $lastVisitedHerbarium): User
    {
        $this->lastVisitedHerbarium = $lastVisitedHerbarium;

        return $this;
    }

    public function initializeCurrentHerbarium(): User
    {
        if ($this->getLastVisitedHerbarium() === null && !empty($this->getUserHerbariumRoles())) {
            $this->setLastVisitedHerbarium($this->getUserHerbariumRoles()[0]);
        }
        return  $this;
    }
}
