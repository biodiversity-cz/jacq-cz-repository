<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a description type with IRI and label.
 */
class DescriptionType implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) ?string $label = null;

    public function __construct()
    {
    }

    // Getters
    public function getIri(): ?string
    {
        return $this->Iri;
    }

    public function getLabel(): ?string
    {
        return $this->Label;
    }

    // Setters
    public function setIri(?string $iri): self
    {
        $this->Iri = $iri;

        return $this;
    }

    public function setLabel(?string $label): self
    {
        $this->Label = $label;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'description_type');

        if (null !== $this->getIri()) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if (null !== $this->getLabel()) {
            $labelElement = $this->createElement($document, 'label', $this->getLabel());
            $element->appendChild($labelElement);
        }

        return $element;
    }
}
