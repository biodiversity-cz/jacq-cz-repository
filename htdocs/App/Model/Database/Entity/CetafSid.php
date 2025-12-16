<?php declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Repository\CetafSidRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: CetafSidRepository::class)]
#[Table(name: 'sid', schema: 'cetaf')]
class CetafSid
{

    use TId;
    use TCreatedAt;
    use TLastEditAt;

    #[Column(unique: true)]
    protected(set) string $stableUri;

    #[Column(nullable: true)]
    protected(set) ?string $scientificNameCurrent = null;

    #[Column(nullable: true)]
    protected(set) ?string $family = null;

    #[Column(nullable: true)]
    protected(set) ?string $scientificNameOriginal = null;

    #[Column(nullable: true)]
    protected(set) ?string $collectorNumber = null;

    #[Column(nullable: true)]
    protected(set) ?string $collectorName = null;

    #[Column(nullable: true)]
    protected(set) ?string $webscaledImageLink = null;

    #[Column(type: "decimal", precision: 10, scale: 7, nullable: true)]
    protected(set) ?string $latitude = null;

    #[Column(type: "decimal", precision: 10, scale: 7, nullable: true)]
    protected(set) ?string $longitude = null;

    #[Column(length: 10, nullable: true)]
    protected(set) ?string $isoCountry = null;

    #[Column(nullable: true)]
    protected(set) ?string $collectionDate = null;

    #[Column(nullable: true)]
    protected(set) ?string $sourceLink = null;

    #[ManyToOne(targetEntity: Herbaria::class)]
    #[JoinColumn(name: 'herbarium', referencedColumnName: 'id', unique: false, nullable: false, options: ['comment' => 'source institution'])]
    protected(set) Herbaria $herbarium;

    public function setStableUri(string $stableUri): CetafSid
    {
        $this->stableUri = $stableUri;
        return $this;
    }

    public function setScientificNameCurrent(?string $scientificNameCurrent): CetafSid
    {
        $this->scientificNameCurrent = $scientificNameCurrent;
        return $this;
    }

    public function setFamily(?string $family): CetafSid
    {
        $this->family = $family;
        return $this;
    }

    public function setScientificNameOriginal(?string $scientificNameOriginal): CetafSid
    {
        $this->scientificNameOriginal = $scientificNameOriginal;
        return $this;
    }
    public function setCollectorNumber(?string $collectorNumber): CetafSid
    {
        $this->collectorNumber = $collectorNumber;
        return $this;
    }

    public function setCollectorName(?string $collectorName): CetafSid
    {
        $this->collectorName = $collectorName;
        return $this;
    }

    public function setWebscaledImageLink(?string $webscaledImageLink): CetafSid
    {
        $this->webscaledImageLink = $webscaledImageLink;
        return $this;
    }


    public function setLatitude(?string $latitude): CetafSid
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function setLongitude(?string $longitude): CetafSid
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function setIsoCountry(?string $isoCountry): CetafSid
    {
        $this->isoCountry = $isoCountry;
        return $this;
    }

    public function setCollectionDate(?string $collectionDate): CetafSid
    {
        $this->collectionDate = $collectionDate;
        return $this;
    }

    public function setSourceLink(?string $sourceLink): CetafSid
    {
        $this->sourceLink = $sourceLink;
        return $this;
    }

    public function setHerbarium(Herbaria $herbarium): CetafSid
    {
        $this->herbarium = $herbarium;
        return $this;
    }


    /**
     *  RDF/XML according to the CSPP (CETAF Specimen Preview Profile)
     */
    public function toRdfXml(): string
    {
        $uri = htmlspecialchars($this->stableUri, ENT_XML1);
        $xml = [];
        $xml[] = '<?xml version="1.0"?>';
        $xml[] = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dwc="http://rs.tdwg.org/dwc/terms/">';
        $xml[] = "  <rdf:Description rdf:about=\"{$uri}\">";
        $xml[] = "    <dcterms:title>" . htmlspecialchars($this->scientificNameCurrent, ENT_XML1) . "</dcterms:title>";
        $xml[] = "    <dcterms:type>" . htmlspecialchars('PreservedSpecimen', ENT_XML1) . "</dcterms:type>";
        $xml[] = "    <dcterms:publisher>" . htmlspecialchars($this->herbarium->address, ENT_XML1) . "</dcterms:publisher>";

//        // sameAs for HTTP variant if exists
//        if ($this->httpAlternateUri) {
//            $alt = htmlspecialchars($this->httpAlternateUri, ENT_XML1);
//            $xml[] = "    <owl:sameAs xmlns:owl=\"http://www.w3.org/2002/07/owl#\">{$alt}</owl:sameAs>";
//        }

        if ($this->scientificNameCurrent !== null) {
            $xml[] = "    <dwc:scientificName>" . htmlspecialchars($this->scientificNameCurrent, ENT_XML1) . "</dwc:scientificName>";
        }
        if ($this->family !== null) {
            $xml[] = "    <dwc:family>" . htmlspecialchars($this->family, ENT_XML1) . "</dwc:family>";
        }
        if ($this->scientificNameOriginal !== null) {
            $xml[] = "    <dwc:originalName>" . htmlspecialchars($this->scientificNameOriginal, ENT_XML1) . "</dwc:originalName>";
        }
        if ($this->collectorNumber !== null) {
            $xml[] = "    <dwc:recordNumber>" . htmlspecialchars($this->collectorNumber, ENT_XML1) . "</dwc:recordNumber>";
        }
        if ($this->collectorName !== null) {
            $xml[] = "    <dcterms:creator>" . htmlspecialchars($this->collectorName, ENT_XML1) . "</dcterms:creator>";
        }
        if ($this->webscaledImageLink !== null) {
            $xml[] = "    <dcterms:hasPart>" . htmlspecialchars($this->webscaledImageLink, ENT_XML1) . "</dcterms:hasPart>";
        }
        if ($this->latitude !== null && $this->longitude !== null) {
            $xml[] = "    <dwc:decimalLatitude>" . $this->latitude . "</dwc:decimalLatitude>";
            $xml[] = "    <dwc:decimalLongitude>" . $this->longitude . "</dwc:decimalLongitude>";
        }
        if ($this->isoCountry !== null) {
            $xml[] = "    <dwc:countryCode>" . htmlspecialchars($this->isoCountry, ENT_XML1) . "</dwc:countryCode>";
        }
        if ($this->collectionDate !== null) {
            $xml[] = "    <dwc:eventDate>" . htmlspecialchars($this->collectionDate, ENT_XML1) . "</dwc:eventDate>";
        }
        if ($this->sourceLink !== null) {
            $xml[] = "    <dcterms:source>" . htmlspecialchars($this->sourceLink, ENT_XML1) . "</dcterms:source>";
        }

        $xml[] = "  </rdf:Description>";
        $xml[] = "</rdf:RDF>";

        return implode("\n", $xml);
    }
}
