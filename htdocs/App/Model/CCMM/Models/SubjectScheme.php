<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a subject scheme with IRI and label.
 */
class SubjectScheme implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) ?string $label = null;

    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'subject_scheme');

        if (null !== $this->iri) {
            $iriElement = $this->createElement($document, 'iri', $this->iri);
            $element->appendChild($iriElement);
        }

        if (null !== $this->label) {
            $labelElement = $this->createElement($document, 'label', $this->label);
            $labelElement->setAttribute('xml:lang', 'en');
            $element->appendChild($labelElement);
        }

        return $element;
    }
}
