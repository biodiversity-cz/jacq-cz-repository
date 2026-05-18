<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents an endpoint URL with IRI and title.
 */
class EndpointUrl implements XmlSerializable
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
        return $this->iri;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    // Setters
    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'endpoint_url');

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
