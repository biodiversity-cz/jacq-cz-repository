<?php

declare(strict_types=1);

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
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Entity(repositoryClass: CetafSidRepository::class)]
#[Table(name: 'sid', schema: 'cetaf')]
#[UniqueConstraint(
    name: 'uniq_herbarium_externalid',
    columns: ['herbarium', 'external_id_from_institution'])]

class CetafSid
{
    use TId;
    use TCreatedAt;
    use TLastEditAt;

    #[Column(options: ['comment' => 'e.g. GUID in case of Museion, something totally persistent and unique across institution, that will allow a redirect in the feature to the original data provider'])]
    public protected(set) string $externalIdFromInstitution;

    #[Column(options: ['comment' => 'specimens identificator that allows match the repository photos'])]
    public protected(set) string $barcode;

    /**
     * CETAFSID:scientificNameCurrent
     * dwc:scientificName.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $scientificName = null;

    /**
     * dwc:identifiedBy.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $identifiedBy = null;

    /**
     * dwc:dateIdentified.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $dateIdentified = null;

    /**
     * CETAFSID:family
     * dwc:family.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $family = null;

    /**
     * CETAFSID:scientificNameOriginal
     * dwc:previousIdentifications.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $previousIdentifications = null;

    /**
     * CETAFSID:collectorNumber
     * dwc:fieldNumber.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $fieldNumber = null;

    /**
     * dwc:locality.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $locality = null;

    /**
     * dwc:verbatimElevation.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $verbatimElevation = null;

    /**
     * CETAFSID:occurrenceRemarks.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $occurrenceRemarks = null;

    /**
     * CETAFSID:collectorName
     * dwc:recordedBy.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $recordedBy = null;
    /**
     * CETAFSID:latitude
     * dwc:decimalLatitude.
     */
    #[Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    public protected(set) ?string $decimalLatitude = null;
    /**
     * CETAFSID:longitude
     * dwc:decimalLongitude.
     */
    #[Column(type: 'decimal', precision: 10, scale: 7, nullable: true)]
    public protected(set) ?string $decimalLongitude = null;
    /**
     * CETAFSID:isoCountry
     * dwc:countryCode.
     */
    #[Column(length: 10, nullable: true)]
    public protected(set) ?string $countryCode = null;
    /**
     * CETAFSID:collectionDate
     * dwc:eventDate
     * dc:created.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $eventDate = null;

    #[ManyToOne(targetEntity: Herbaria::class)]
    #[JoinColumn(name: 'herbarium', referencedColumnName: 'id', nullable: false, options: ['comment' => 'source institution'])]
    public protected(set) Herbaria $herbarium;

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

    private function safeH($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1);
    }

    private function xmlElement(string $tag, mixed $value): ?string
    {
        if (null === $value || '' === $value) {
            return null; // element nebude vytvořen
        }

        return "    <{$tag}>".$this->safeH($value)."</{$tag}>";
    }

    private function formatDateIso(string|\DateTimeImmutable|null $date): ?string
    {
        if (null === $date) {
            return null;
        }

        // pokud už je to DateTime, použijeme ho přímo
        if ($date instanceof \DateTimeImmutable) {
            return $date->format('Y-m-d');
        }

        // zkusíme vytvořit DateTime z řetězce
        try {
            $dt = new \DateTimeImmutable($date);

            return $dt->format('Y-m-d');
        } catch (\Exception $e) {
            // nelze parsovat, vrátíme null nebo původní string
            return null;
        }
    }

    /**
     *  RDF/XML according to the CSPP (CETAF Specimen Preview Profile).
     */
    public function toRdfXml(string $uri): string
    {
        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dwc="http://rs.tdwg.org/dwc/terms/">';
        $xml[] = "  <rdf:Description rdf:about=\"{$uri}\">";

        // povinné
        $xml[] = $this->xmlElement('dcterms:title', $this->scientificName);
        $xml[] = $this->xmlElement('dcterms:type', 'PreservedSpecimen');

        // volitelné
        $xml[] = $this->xmlElement('dcterms:publisher', $this->herbarium?->address);
        $xml[] = $this->xmlElement('dwc:scientificName', $this->scientificName);
        $xml[] = $this->xmlElement('dwc:family', $this->family);
        $xml[] = $this->xmlElement('dwc:originalName', $this->previousIdentifications);
        $xml[] = $this->xmlElement('dwc:recordNumber', $this->fieldNumber);
        $xml[] = $this->xmlElement('dcterms:creator', $this->recordedBy);

        // koordináty
        if (null !== $this->decimalLatitude && null !== $this->decimalLongitude) {
            $xml[] = "    <dwc:decimalLatitude>{$this->decimalLatitude}</dwc:decimalLatitude>";
            $xml[] = "    <dwc:decimalLongitude>{$this->decimalLongitude}</dwc:decimalLongitude>";
        }

        $xml[] = $this->xmlElement('dwc:countryCode', $this->countryCode);

        if (null !== $this->eventDate) {
            $isoDate = $this->formatDateIso($this->eventDate);
            if (null !== $isoDate) {
                $xml[] = $this->xmlElement('dwc:eventDate', $isoDate);
            }
        }

        $xml[] = '  </rdf:Description>';
        $xml[] = '</rdf:RDF>';

        // odstraníme null řádky
        return implode("\n", array_filter($xml));
    }
}
