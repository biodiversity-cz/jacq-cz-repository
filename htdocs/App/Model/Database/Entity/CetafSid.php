<?php declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Repository\CetafSidRepository;
use Doctrine\DBAL\Schema\UniqueConstraint;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: CetafSidRepository::class)]
#[Table(name: 'sid', schema: 'cetaf',
    uniqueConstraints: [
        new UniqueConstraint(
            name: "uniq_herbarium_externalid",
            columns: ["herbarium", "external_id_from_institution"]
        )
    ])]

class CetafSid
{

    use TId;
    use TCreatedAt;
    use TLastEditAt;

    #[Column(options: ['comment' => 'e.g. GUID in case of Museion, something totally persistent and unique across institution, that will allow a redirect in the feature to the original data provider'])]
    protected(set) string $externalIdFromInstitution;

    #[Column(options: ['comment' => 'specimens identificator that allows match the repository photos'])]
    protected(set) string $barcode;

    #[Column(unique: true, nullable: true, options: ['comment' => 'stable URI assigned to the specimen'])]
    protected(set) ?string $stableUri;

    /**
     * CETAFSID:scientificNameCurrent
     * dwc:scientificName
     */
    #[Column(nullable: true)]
    protected(set) ?string $scientificName = null;

    /**
     * dwc:identifiedBy
     */
    #[Column(nullable: true)]
    protected(set) ?string $identifiedBy = null;

    /**
     * dwc:dateIdentified
     */
    #[Column(nullable: true)]
    protected(set) ?string $dateIdentified = null;

    /**
     * CETAFSID:family
     * dwc:family
     */
    #[Column(nullable: true)]
    protected(set) ?string $family = null;
    /**
     * CETAFSID:scientificNameOriginal
     * dwc:previousIdentifications
     */
    #[Column(nullable: true)]
    protected(set) ?string $previousIdentifications = null;

    /**
     * dwc:verbatimIdentification
     */
    #[Column(nullable: true)]
    protected(set) ?string $verbatimIdentification = null;

    /**
     * CETAFSID:collectorNumber
     * dwc:fieldNumber
     */
    #[Column(nullable: true)]
    protected(set) ?string $fieldNumber = null;

    /**
     * dwc:locality
     */
    #[Column(nullable: true)]
    protected(set) ?string $locality = null;

    /**
     * dwc:verbatimElevation
     */
    #[Column(nullable: true)]
    protected(set) ?string $verbatimElevation = null;

    /**
     * CETAFSID:occurrenceRemarks
     */
    #[Column(nullable: true)]
    protected(set) ?string $occurrenceRemarks = null;

    /**
     * CETAFSID:collectorName
     * dwc:recordedBy
     */
    #[Column(nullable: true)]
    protected(set) ?string $recordedBy = null;
    /**
     * CETAFSID:latitude
     * dwc:decimalLatitude
     */
    #[Column(type: "decimal", precision: 10, scale: 7, nullable: true)]
    protected(set) ?string $decimalLatitude = null;
    /**
     * CETAFSID:longitude
     * dwc:decimalLongitude
     */
    #[Column(type: "decimal", precision: 10, scale: 7, nullable: true)]
    protected(set) ?string $decimalLongitude = null;
    /**
     * CETAFSID:isoCountry
     * dwc:countryCode
     */
    #[Column(length: 10, nullable: true)]
    protected(set) ?string $countryCode = null;
    /**
     * CETAFSID:collectionDate
     * dwc:eventDate
     * dc:created
     */
    #[Column(nullable: true)]
    protected(set) ?string $eventDate = null;

    #[ManyToOne(targetEntity: Herbaria::class)]
    #[JoinColumn(name: 'herbarium', referencedColumnName: 'id', nullable: false, options: ['comment' => 'source institution'])]
    protected(set) Herbaria $herbarium;

    public function setExternalIdFromInstitution(string $externalIdFromInstitution): CetafSid
    {
        $this->externalIdFromInstitution = $externalIdFromInstitution;
        return $this;
    }

    public function setBarcode(string $barcode): CetafSid
    {
        $this->barcode = $barcode;
        return $this;
    }

    public function setStableUri(?string $stableUri): CetafSid
    {
        $this->stableUri = $stableUri;
        return $this;
    }

    public function setScientificName(?string $scientificName): CetafSid
    {
        $this->scientificName = $scientificName;
        return $this;
    }

    public function setIdentifiedBy(?string $identifiedBy): CetafSid
    {
        $this->identifiedBy = $identifiedBy;
        return $this;
    }

    public function setDateIdentified(?string $dateIdentified): CetafSid
    {
        $this->dateIdentified = $dateIdentified;
        return $this;
    }

    public function setFamily(?string $family): CetafSid
    {
        $this->family = $family;
        return $this;
    }

    public function setVerbatimIdentification(?string $verbatimIdentification): CetafSid
    {
        $this->verbatimIdentification = $verbatimIdentification;
        return $this;
    }

    public function setPreviousIdentifications(?string $previousIdentifications): CetafSid
    {
        $this->previousIdentifications = $previousIdentifications;
        return $this;
    }

    public function setFieldNumber(?string $fieldNumber): CetafSid
    {
        $this->fieldNumber = $fieldNumber;
        return $this;
    }

    public function setLocality(?string $locality): CetafSid
    {
        $this->locality = $locality;
        return $this;
    }

    public function setVerbatimElevation(?string $verbatimElevation): CetafSid
    {
        $this->verbatimElevation = $verbatimElevation;
        return $this;
    }

    public function setOccurrenceRemarks(?string $occurrenceRemarks): CetafSid
    {
        $this->occurrenceRemarks = $occurrenceRemarks;
        return $this;
    }

    public function setRecordedBy(?string $recordedBy): CetafSid
    {
        $this->recordedBy = $recordedBy;
        return $this;
    }

    public function setDecimalLatitude(?string $decimalLatitude): CetafSid
    {
        $this->decimalLatitude = $decimalLatitude;
        return $this;
    }

    public function setDecimalLongitude(?string $decimalLongitude): CetafSid
    {
        $this->decimalLongitude = $decimalLongitude;
        return $this;
    }

    public function setCountryCode(?string $countryCode): CetafSid
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function setEventDate(?string $eventDate): CetafSid
    {
        $this->eventDate = $eventDate;
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
        $xml[] = "    <dcterms:title>" . htmlspecialchars($this->scientificName, ENT_XML1) . "</dcterms:title>";
        $xml[] = "    <dcterms:type>" . htmlspecialchars('PreservedSpecimen', ENT_XML1) . "</dcterms:type>";
        $xml[] = "    <dcterms:publisher>" . htmlspecialchars($this->herbarium->address, ENT_XML1) . "</dcterms:publisher>";

//        // sameAs for HTTP variant if exists
//        if ($this->httpAlternateUri) {
//            $alt = htmlspecialchars($this->httpAlternateUri, ENT_XML1);
//            $xml[] = "    <owl:sameAs xmlns:owl=\"http://www.w3.org/2002/07/owl#\">{$alt}</owl:sameAs>";
//        }

        if ($this->scientificName !== null) {
            $xml[] = "    <dwc:scientificName>" . htmlspecialchars($this->scientificName, ENT_XML1) . "</dwc:scientificName>";
        }
        if ($this->family !== null) {
            $xml[] = "    <dwc:family>" . htmlspecialchars($this->family, ENT_XML1) . "</dwc:family>";
        }
        if ($this->verbatimIdentification !== null) {
            $xml[] = "    <dwc:originalName>" . htmlspecialchars($this->verbatimIdentification, ENT_XML1) . "</dwc:originalName>";
        }
        if ($this->fieldNumber !== null) {
            $xml[] = "    <dwc:recordNumber>" . htmlspecialchars($this->fieldNumber, ENT_XML1) . "</dwc:recordNumber>";
        }
        if ($this->recordedBy !== null) {
            $xml[] = "    <dcterms:creator>" . htmlspecialchars($this->recordedBy, ENT_XML1) . "</dcterms:creator>";
        }

            $xml[] = "    <dcterms:hasPart>" . htmlspecialchars('XXX', ENT_XML1) . "</dcterms:hasPart>";

        if ($this->decimalLatitude !== null && $this->decimalLongitude !== null) {
            $xml[] = "    <dwc:decimalLatitude>" . $this->decimalLatitude . "</dwc:decimalLatitude>";
            $xml[] = "    <dwc:decimalLongitude>" . $this->decimalLongitude . "</dwc:decimalLongitude>";
        }
        if ($this->countryCode !== null) {
            $xml[] = "    <dwc:countryCode>" . htmlspecialchars($this->countryCode, ENT_XML1) . "</dwc:countryCode>";
        }
        if ($this->eventDate !== null) {
            $xml[] = "    <dwc:eventDate>" . htmlspecialchars($this->eventDate, ENT_XML1) . "</dwc:eventDate>";
        }

            $xml[] = "    <dcterms:source>" . htmlspecialchars('XXX', ENT_XML1) . "</dcterms:source>";


        $xml[] = "  </rdf:Description>";
        $xml[] = "</rdf:RDF>";

        return implode("\n", $xml);
    }
}
