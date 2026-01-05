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
    protected(set) ?string $pid = null;

    #[Column(nullable: true)]
    protected(set) ?string $family = null;

    #[Column(nullable: true)]
    protected(set) ?string $taxon = null;

    #[Column(nullable: true)]
    protected(set) ?string $country = null;

    #[Column(nullable: true)]
    protected(set) ?float $lat = null;

    #[Column( nullable: true)]
    protected(set) ?float $lon = null;

    #[Column(nullable: true)]
    protected(set) ?string $collection = null;

    #[ManyToOne(targetEntity: Photos::class, inversedBy: 'specimenMetadata')]
    #[JoinColumn(name: 'photo_id', referencedColumnName: 'id', nullable: true)]
    protected(set) ?Photos $photo = null;

    public function setPid(?string $pid): SpecimenMetadata
    {
        $this->pid = $pid;
        return $this;
    }

    public function setFamily(?string $family): SpecimenMetadata
    {
        $this->family = $family;
        return $this;
    }

    public function setTaxon(?string $taxon): SpecimenMetadata
    {
        $this->taxon = $taxon;
        return $this;
    }

    public function setCountry(?string $country): SpecimenMetadata
    {
        $this->country = $country;
        return $this;
    }

    public function setLat(?string $lat): SpecimenMetadata
    {
        $this->lat = $lat;
        return $this;
    }

    public function setLon(?string $lon): SpecimenMetadata
    {
        $this->lon = $lon;
        return $this;
    }

    public function setCollection(?string $collection): SpecimenMetadata
    {
        $this->collection = $collection;
        return $this;
    }

    public function setPhoto(?Photos $photo): SpecimenMetadata
    {
        $this->photo = $photo;
        return $this;
    }

}
