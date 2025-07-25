<?php declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Enums\DatabotRole;
use App\Model\Database\Enums\EnumDatabotRole;
use App\Model\Database\Repository\DatabotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: DatabotRepository::class)]
#[Table(name: 'databot')]
#[UniqueConstraint(columns: ['name', 'version'],options: ['comment' => 'List of bots registered'])]
class Databot
{

    use TId;
    use TCreatedAt;

    #[Column(nullable: false, options: ['comment' => 'Short name of Databot'])]
    protected string $name;

    #[Column(type: Types::TEXT, length: 60000, unique: false, nullable: false)]
    protected string $description;

    #[Column(nullable: false)]
    protected int $version;

    #[Column(type: 'boolean', nullable: false, options: ['default' => true])]
    protected bool $enabled = true;

    #[Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $lastRun = null;

    #[Column(
        type: EnumDatabotRole::NAME,
        nullable: false,
        enumType: DatabotRole::class,
        options: ['default' => DatabotRole::VALIDATOR])]
    protected DatabotRole $role = DatabotRole::VALIDATOR;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Databot
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Databot
    {
        $this->description = $description;
        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): Databot
    {
        $this->version = $version;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): Databot
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getLastRun(): ?\DateTimeInterface
    {
        return $this->lastRun;
    }

    public function setLastRun(?\DateTimeInterface $lastRun): Databot
    {
        $this->lastRun = $lastRun;
        return $this;
    }

    public function getRole(): DatabotRole
    {
        return $this->role;
    }

    public function setRole(DatabotRole $role): Databot
    {
        $this->role = $role;
        return $this;
    }

}
