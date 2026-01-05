<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents an endpoint URL with IRI and title
 */
class EndpointUrl implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?string $iri = null;
    protected(set) ?string $title = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->iri;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->iri = $iri;
        return $this;
    }

    public function setTitle(?string $title): self {
        $this->title = $title;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'endpoint_url');

        if ($this->getIri() !== null) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if ($this->getTitle() !== null) {
            $titleElement = $this->createElement($document, 'title', $this->getTitle());
            $element->appendChild($titleElement);
        }

        return $element;
    }
}
