<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a role with IRI and label
 */
class Role implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?string $iri = null;
    protected(set) ?string $label = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->Iri;
    }

    public function getLabel(): ?string {
        return $this->Label;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->Iri = $iri;
        return $this;
    }

    public function setLabel(?string $label): self {
        $this->Label = $label;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'role');

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
