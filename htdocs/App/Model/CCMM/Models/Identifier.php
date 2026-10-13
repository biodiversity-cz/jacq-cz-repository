<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents an identifier with IRI, value and scheme.
 */
class Identifier implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) ?string $value = null;
    public protected(set) ?IdentifierScheme $scheme = null;

    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setScheme(?IdentifierScheme $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'identifier');

        if (null !== $this->iri) {
            $iriElement = $this->createElement($document, 'iri', $this->iri);
            $element->appendChild($iriElement);
        }

        if (null !== $this->value) {
            $valueElement = $this->createElement($document, 'value', $this->value);
            $element->appendChild($valueElement);
        }

        $this->appendChildIfNotNull($element, $this->scheme, 'scheme');

        return $element;
    }
}
