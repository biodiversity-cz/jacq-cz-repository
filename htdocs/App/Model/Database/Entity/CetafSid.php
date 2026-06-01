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

    /**
     * dwc:verbatimEventDate.
     */
    #[Column(nullable: true)]
    public protected(set) ?string $verbatimEventDate = null;

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

    public function setVerbatimEventDate(?string $verbatimEventDate): CetafSid
    {
        $this->verbatimEventDate = $verbatimEventDate;

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

    /**
     * the catalogue number is barcode without a herbarium acronym
     */
    public function getCatalogueNumber()
    {
        return trim(substr($this->barcode, strlen($this->herbarium->acronym)));
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
    public function toRdfXml(string $rdfUri, string $sidUri): string
    {
        $xml = [];

        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';

        $xml[] = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/terms/" xmlns:dwc="http://rs.tdwg.org/dwc/terms/">';

        //metadata
        $xml[] = "  <rdf:Description rdf:about=\"{$rdfUri}\">";
        $xml[] = '    <dc:subject rdf:resource="'.$sidUri.'"></dc:subject>';
        $xml[] = $this->xmlElement ("dc:created", new \DateTimeImmutable()->format(DATE_ATOM));
        $xml[] = '  </rdf:Description>';

        $xml[] = "  <rdf:Description rdf:about=\"{$sidUri}\">";
        // povinné
        $xml[] = $this->xmlElement('dc:title', $this->scientificName);
        $xml[] = $this->xmlElement('dc:type', 'PreservedSpecimen');

        // volitelné
        $xml[] = $this->xmlElement('dc:publisher', $this->herbarium?->address);
        $xml[] = $this->xmlElement('dwc:scientificName', $this->scientificName);
        $xml[] = $this->xmlElement('dwc:family', $this->family);
        $xml[] = $this->xmlElement('dwc:originalName', $this->previousIdentifications);
        $xml[] = $this->xmlElement('dwc:previousIdentifications', $this->previousIdentifications);
        $xml[] = $this->xmlElement('dwc:recordNumber', $this->fieldNumber);
        $xml[] = $this->xmlElement('dc:creator', $this->recordedBy);
        $xml[] = $this->xmlElement('dwc:recordedBy', $this->recordedBy);
        $xml[] = $this->xmlElement('dwc:family', $this->family);
        $xml[] = $this->xmlElement('dwc:countryCode', $this->countryCode);
        $xml[] = $this->xmlElement('dwc:collectionCode', $this->herbarium->acronym);
        $xml[] = $this->xmlElement('dwc:material_sample_id', $sidUri);
        $xml[] = $this->xmlElement('dwc:catalogNumber', $this->getCatalogueNumber());

        $xml[] = $this->xmlElement('dwc:locality', $this->locality);
        if (null !== $this->decimalLatitude && null !== $this->decimalLongitude) {
            $xml[] = "    <dwc:decimalLatitude>{$this->decimalLatitude}</dwc:decimalLatitude>";
            $xml[] = "    <dwc:decimalLongitude>{$this->decimalLongitude}</dwc:decimalLongitude>";
        }


        $xml[] = $this->xmlElement('dwc:countryCode', $this->countryCode);

        if (null !== $this->eventDate) {
            $isoDate = $this->formatDateIso($this->eventDate);
            if (null !== $isoDate) {
                $xml[] = $this->xmlElement('dwc:eventDate', $isoDate);
            } else {
                $xml[] = $this->xmlElement('dwc:verbatimEventDate', $this->verbatimEventDate);
            }
        }

        $xml[] = '  </rdf:Description>';
        $xml[] = '</rdf:RDF>';

        // odstraníme null řádky
        return implode("\n", array_filter($xml));
    }
}
