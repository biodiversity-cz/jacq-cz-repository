<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents an organization with name and identifier.
 */
class Organization implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) ?Identifier $identifier = null;
    public protected(set) ?string $name = null;

    public function __construct()
    {
    }

    // Getters
    public function getIri(): ?string
    {
        return $this->iri;
    }

    public function getIdentifier(): ?Identifier
    {
        return $this->identifier;
    }

    // Setters
    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function setIdentifier(?Identifier $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'organization');

        if (null !== $this->getIri()) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        $this->appendChildIfNotNull($element, $this->getIdentifier());

        if (null !== $this->name) {
            $nameElement = $this->createElement($document, 'name', $this->name);
            $element->appendChild($nameElement);
        }

        return $element;
    }
}
