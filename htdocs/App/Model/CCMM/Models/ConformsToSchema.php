<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a conforms to schema with IRI and label
 */
class ConformsToSchema implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?string $iri = null;
    protected(set) ?string $label = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->iri;
    }

    public function getLabel(): ?string {
        return $this->label;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->iri = $iri;
        return $this;
    }

    public function setLabel(?string $label): self {
        $this->label = $label;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'conforms_to_schema');

        if ($this->getIri() !== null) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if ($this->getLabel() !== null) {
            $labelElement = $this->createElement($document, 'label', $this->getLabel());
            $labelElement->setAttribute('xml:lang', 'en');
            $element->appendChild($labelElement);
        }

        return $element;
    }
}
