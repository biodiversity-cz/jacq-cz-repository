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
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
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

    #[Column(nullable: true, options: ['comment' => 'OpenID subject identifier'])]
    protected ?string $openidSubject = null;

    #[Column(nullable: true, options: ['comment' => 'OpenID provider'])]
    protected ?string $openidProvider = null;

    #[Column(nullable: true, options: ['comment' => 'OpenID ID token'])]
    protected ?string $openidIdToken = null;

    #[Column(nullable: true, options: ['comment' => 'OpenID refresh token'])]
    protected ?string $openidRefreshToken = null;

    #[ManyToOne(targetEntity: Herbaria::class)]
    #[JoinColumn(name: 'last_visited_herbarium',   referencedColumnName: 'id', nullable: true, options: ['comment' => 'Last visited herbarium'])]
    protected ?Herbaria $lastVisitedHerbarium = null;

    #[ManyToOne(targetEntity: UserRole::class)]
    #[JoinColumn(name: 'role_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Role for ACL'])]
    protected UserRole $role;

    #[Column(type: Types::BOOLEAN, nullable: false, options: ['comment' => 'Option to disable access for a specific user'])]
    protected bool $active = true;

    #[Column(type: Types::TEXT, length: 60000, nullable: true, options: ['comment' => 'additional information about user'])]
    protected ?string $comment;

    #[ManyToMany(targetEntity: Herbaria::class)]
    #[JoinTable(name: 'user_herbaria')]
    protected Collection $herbariums;

    public function __construct()
    {
        $this->herbariums = new ArrayCollection();
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

    public function getRole(): UserRole
    {
        return $this->role;
    }

    public function setRole(UserRole $role): User
    {
        $this->role = $role;

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

    public function getHerbariums(): Collection
    {
        return $this->herbariums;
    }

    public function addHerbarium(Herbaria $herbarium): User
    {
        if (!$this->herbariums->contains($herbarium)) {
            $this->herbariums->add($herbarium);
        }

        return $this;
    }

    public function removeHerbarium(Herbaria $herbarium): User
    {
        $this->herbariums->removeElement($herbarium);

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
}
