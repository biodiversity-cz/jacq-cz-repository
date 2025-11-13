<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'external_database')]
class ExternalDatabase
{
    use TId;

    public const int JACQ = 1;
    #[Column(type: Types::STRING, unique: true, nullable: false)]
    protected string $name;

    #[Column(type: Types::STRING, unique: true, nullable: false)]
    protected string $url;

    #[Column(type: Types::STRING, unique: true, nullable: false)]
    protected string $element;

    #[Column(type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ExternalDatabase
    {
        $this->name = $name;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): ExternalDatabase
    {
        $this->url = $url;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): ExternalDatabase
    {
        $this->description = $description;
        return $this;
    }

    public function getElement(): string
    {
        return $this->element;
    }

    public function setElement(string $element): ExternalDatabase
    {
        $this->element = $element;
        return $this;
    }

}
