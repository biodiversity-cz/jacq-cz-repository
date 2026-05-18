<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Enums\DatabotRole;
use App\Model\Database\Repository\DatabotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: DatabotRepository::class)]
#[Table(name: 'databot', schema: 'databots')]
#[UniqueConstraint(columns: ['name', 'version'], options: ['comment' => 'List of bots registered'])]
class Databot
{
    use TId;
    use TCreatedAt;

    #[Column(nullable: false, options: ['comment' => 'Short name of Databot'])]
    public protected(set) string $name;

    #[Column(type: Types::TEXT, length: 60000, unique: false, nullable: false)]
    public protected(set) string $description;

    #[Column(nullable: false)]
    public protected(set) int $version;

    #[Column(type: 'boolean', nullable: false, options: ['default' => true])]
    public protected(set) bool $enabled = true;

    #[Column(type: 'datetime', nullable: true)]
    public protected(set) ?\DateTimeInterface $lastRun = null;

    #[Column(
        nullable: false,
        enumType: DatabotRole::class,
        options: ['default' => DatabotRole::VALIDATOR])]
    public protected(set) DatabotRole $role = DatabotRole::VALIDATOR;

    public function setName(string $name): Databot
    {
        $this->name = $name;

        return $this;
    }

    public function setDescription(string $description): Databot
    {
        $this->description = $description;

        return $this;
    }

    public function setVersion(int $version): Databot
    {
        $this->version = $version;

        return $this;
    }

    public function setEnabled(bool $enabled): Databot
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function setLastRun(?\DateTimeInterface $lastRun): Databot
    {
        $this->lastRun = $lastRun;

        return $this;
    }

    public function setRole(DatabotRole $role): Databot
    {
        $this->role = $role;

        return $this;
    }
}
