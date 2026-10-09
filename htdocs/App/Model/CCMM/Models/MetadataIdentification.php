<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;
use App\UI\Front\OaiPmh\OaiPmhPresenter;

/**
 * Represents metadata identification information.
 */
class MetadataIdentification implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * @param QualifiedAttribution[] $qualifiedAttributions
     */
    public function __construct(
        public protected(set) ?string              $iri = null,
        public protected(set)  ?Language           $language = null,
        public protected(set)  array               $qualifiedAttributions = [],
        public protected(set)  ?\DateTime             $dateUpdated = null,
        public protected(set)  ?\DateTimeImmutable             $dateCreated = null,
        public protected(set)  ?ConformsToStandard $conformsToStandard = null,
        public protected(set)  ?OriginalRepository $originalRepository = null,
    ) {
    }

    public function setIri(?string $iri): MetadataIdentification
    {
        $this->iri = $iri;
        return $this;
    }

    public function setLanguage(?Language $language): MetadataIdentification
    {
        $this->language = $language;
        return $this;
    }

    public function setQualifiedAttributions(array $qualifiedAttributions): MetadataIdentification
    {
        $this->qualifiedAttributions = $qualifiedAttributions;
        return $this;
    }

    public function setDateUpdated(?\DateTime $dateUpdated): MetadataIdentification
    {
        $this->dateUpdated = $dateUpdated;
        return $this;
    }

    public function setDateCreated(?\DateTimeImmutable $dateCreated): MetadataIdentification
    {
        $this->dateCreated = $dateCreated;
        return $this;
    }

    public function setConformsToStandard(?ConformsToStandard $conformsToStandard): MetadataIdentification
    {
        $this->conformsToStandard = $conformsToStandard;
        return $this;
    }

    public function setOriginalRepository(?OriginalRepository $originalRepository): MetadataIdentification
    {
        $this->originalRepository = $originalRepository;
        return $this;
    }



    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'metadata_identification');

        if (null !== $this->iri) {
            $iriElement = $this->createElement($document, 'iri', $this->iri);
            $element->appendChild($iriElement);
        }

        $this->appendChildIfNotNull($element, $this->language);

        foreach ($this->qualifiedAttributions as $qualifiedRelation) {
            $qualifiedRelationElement = $qualifiedRelation->toXml($document);
            $element->appendChild($qualifiedRelationElement);
        }

        if (null !== $this->dateUpdated) {
            $dateUpdatedElement = $this->createElement($document, 'date_updated', $this->dateUpdated->format('Y-m-d'));
            $element->appendChild($dateUpdatedElement);
        }

        if (null !== $this->dateCreated) {
            $dateCreatedElement = $this->createElement($document, 'date_created', $this->dateCreated->format('Y-m-d'));
            $element->appendChild($dateCreatedElement);
        }

        $this->appendChildIfNotNull($element, $this->conformsToStandard, 'conforms_to_standard');
        $this->appendChildIfNotNull($element, $this->originalRepository, 'original_repository');

        return $element;
    }
}
