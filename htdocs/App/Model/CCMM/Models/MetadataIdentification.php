<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents metadata identification information.
 */
class MetadataIdentification implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * @param QualifiedRelation[] $qualifiedRelations
     */
    public function __construct(
        public ?string $iri = null,
        public ?Language $language = null,
        public array $qualifiedRelations = [],
        public ?string $dateUpdated = null,
        public ?string $dateCreated = null,
        public ?ConformsToStandard $conformsToStandard = null,
        public ?OriginalRepository $originalRepository = null,
    ) {
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'metadata_identification');

        if (null !== $this->getIri()) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        $this->appendChildIfNotNull($element, $this->getLanguage());

        foreach ($this->getQualifiedRelations() as $qualifiedRelation) {
            $qualifiedRelationElement = $qualifiedRelation->toXml($document);
            $element->appendChild($qualifiedRelationElement);
        }

        if (null !== $this->getDateUpdated()) {
            $dateUpdatedElement = $this->createElement($document, 'date_updated', $this->getDateUpdated());
            $element->appendChild($dateUpdatedElement);
        }

        if (null !== $this->getDateCreated()) {
            $dateCreatedElement = $this->createElement($document, 'date_created', $this->getDateCreated());
            $element->appendChild($dateCreatedElement);
        }

        $this->appendChildIfNotNull($element, $this->getConformsToStandard(), 'conforms_to_standard');
        $this->appendChildIfNotNull($element, $this->getOriginalRepository(), 'original_repository');

        return $element;
    }
}
