<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a related object with IRI and title.
 */
class RelatedObject implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) ?string $title = null;

    public function __construct()
    {
    }

    // Getters
    public function getIri(): ?string
    {
        return $this->Iri;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    // Setters
    public function setIri(?string $iri): self
    {
        $this->Iri = $iri;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->Title = $title;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'related_object');

        if (null !== $this->getIri()) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if (null !== $this->getTitle()) {
            $titleElement = $this->createElement($document, 'title', $this->getTitle());
            $element->appendChild($titleElement);
        }

        return $element;
    }
}
