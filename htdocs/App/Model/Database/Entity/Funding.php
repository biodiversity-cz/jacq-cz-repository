<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Entity\Herbaria;
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
    protected string $name;

    #[Column(type: Types::STRING, unique: false, nullable: true)]
    protected ?string $code;

    #[Column(type: Types::STRING, unique: false, nullable: true)]
    protected ?string $funder;

    #[Column(type: Types::TEXT, unique: false, nullable: true)]
    protected ?string $description = null;

    #[Column(type: Types::TEXT, unique: false, nullable: true)]
    protected ?string $note = null;

    #[ManyToOne(targetEntity: Herbaria::class)]
    #[JoinColumn(name: 'herbarium_id', referencedColumnName: 'id', nullable: true)]
    protected ?Herbaria $herbarium = null;

    #[Column(type: Types::BOOLEAN, unique: false, nullable: false, options: ['default' => true])]
    protected bool $active = true;

    #[Column(name:'ccmm_format', type: Types::TEXT, nullable: true, options: ['comment' => 'Structured XML data for OAI-PMH CCMM export'])]
    protected ?string $ccmmFormat = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Funding
    {
        $this->name = $name;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): Funding
    {
        $this->code = $code;
        return $this;
    }

    public function getFunder(): ?string
    {
        return $this->funder;
    }

    public function setFunder(?string $funder): Funding
    {
        $this->funder = $funder;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): Funding
    {
        $this->description = $description;
        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): Funding
    {
        $this->note = $note;
        return $this;
    }

    public function getHerbarium(): ?Herbaria
    {
        return $this->herbarium;
    }

    public function setHerbarium(?Herbaria $herbarium): Funding
    {
        $this->herbarium = $herbarium;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): Funding
    {
        $this->active = $active;
        return $this;
    }

    public function getCcmmFormat(): ?string
    {
        return $this->ccmmFormat;
    }

    public function setCcmmFormat(?string $ccmmFormat): Funding
    {
        $this->ccmmFormat = $ccmmFormat;
        return $this;
    }

    public function getFullname():string
    {
        return $this->getName() . ' (' . $this->getCode() . ')';
    }



}
