<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Repository\FundingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: FundingRepository::class)]
#[Table(name: 'funding', schema: 'public')]
class Funding
{
    use TId;
    use TCreatedAt;
    use TLastEditAt;

    #[Column(type: Types::STRING, unique: false, nullable: false)]
    public protected(set) string $name;

    #[Column(type: Types::STRING, unique: false, nullable: true)]
    public protected(set) ?string $code;

    #[Column(type: Types::STRING, unique: false, nullable: true)]
    public protected(set) ?string $funder;

    #[Column(type: Types::TEXT, unique: false, nullable: true)]
    public protected(set) ?string $description = null;

    #[Column(type: Types::TEXT, unique: false, nullable: true)]
    public protected(set) ?string $note = null;

    #[ManyToOne(targetEntity: Herbaria::class)]
    #[JoinColumn(name: 'herbarium_id', referencedColumnName: 'id', nullable: true)]
    public protected(set) ?Herbaria $herbarium = null;

    #[Column(type: Types::BOOLEAN, unique: false, nullable: false, options: ['default' => true])]
    public protected(set) bool $active = true;

    #[Column(name: 'ccmm_format', type: Types::TEXT, nullable: true, options: ['comment' => 'Structured XML data for OAI-PMH CCMM export'])]
    public protected(set) ?string $ccmmFormat = null;

    public function setName(string $name): Funding
    {
        $this->name = $name;

        return $this;
    }

    public function setCode(?string $code): Funding
    {
        $this->code = $code;

        return $this;
    }

    public function setFunder(?string $funder): Funding
    {
        $this->funder = $funder;

        return $this;
    }

    public function setDescription(?string $description): Funding
    {
        $this->description = $description;

        return $this;
    }

    public function setNote(?string $note): Funding
    {
        $this->note = $note;

        return $this;
    }

    public function setHerbarium(?Herbaria $herbarium): Funding
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function setActive(bool $active): Funding
    {
        $this->active = $active;

        return $this;
    }

    public function setCcmmFormat(?string $ccmmFormat): Funding
    {
        $this->ccmmFormat = $ccmmFormat;

        return $this;
    }

    public function getFullname(): string
    {
        return $this->name.' ('.$this->code.')';
    }
}
