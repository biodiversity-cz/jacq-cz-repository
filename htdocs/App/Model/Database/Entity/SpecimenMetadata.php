<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'specimen_metadata', schema: 'cache')]
class SpecimenMetadata
{

    use TId;
    use TCreatedAt;

    #[Column(nullable: true)]
    private ?string $pid = null;

    #[Column(nullable: true)]
    private ?string $family = null;

    #[Column(nullable: true)]
    private ?string $taxon = null;

    #[Column(nullable: true)]
    private ?string $country = null;

    #[Column(nullable: true)]
    private ?float $lat = null;

    #[Column( nullable: true)]
    private ?float $lon = null;

    #[Column(nullable: true)]
    private ?string $collection = null;

    #[ManyToOne(targetEntity: Photos::class, inversedBy: 'specimenMetadata')]
    #[JoinColumn(name: 'photo_id', referencedColumnName: 'id', nullable: true)]
    private ?Photos $photo = null;

    public function getPid(): ?string
    {
        return $this->pid;
    }

    public function setPid(?string $pid): SpecimenMetadata
    {
        $this->pid = $pid;
        return $this;
    }

    public function getFamily(): ?string
    {
        return $this->family;
    }

    public function setFamily(?string $family): SpecimenMetadata
    {
        $this->family = $family;
        return $this;
    }

    public function getTaxon(): ?string
    {
        return $this->taxon;
    }

    public function setTaxon(?string $taxon): SpecimenMetadata
    {
        $this->taxon = $taxon;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): SpecimenMetadata
    {
        $this->country = $country;
        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(?string $lat): SpecimenMetadata
    {
        $this->lat = $lat;
        return $this;
    }

    public function getLon(): ?string
    {
        return $this->lon;
    }

    public function setLon(?string $lon): SpecimenMetadata
    {
        $this->lon = $lon;
        return $this;
    }

    public function getCollection(): ?string
    {
        return $this->collection;
    }

    public function setCollection(?string $collection): SpecimenMetadata
    {
        $this->collection = $collection;
        return $this;
    }

    public function getPhoto(): ?Photos
    {
        return $this->photo;
    }

    public function setPhoto(?Photos $photo): SpecimenMetadata
    {
        $this->photo = $photo;
        return $this;
    }

}
