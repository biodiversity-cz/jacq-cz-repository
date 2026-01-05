<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents documentation with IRI
 */
class Documentation implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?string $iri = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->iri;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->iri = $iri;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'documentation');

        if ($this->getIri() !== null) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        return $element;
    }
}
