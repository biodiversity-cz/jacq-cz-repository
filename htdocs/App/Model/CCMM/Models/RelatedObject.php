<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a related object with IRI and title
 */
class RelatedObject implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $iri = null;
    private ?string $title = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->Iri;
    }

    public function getTitle(): ?string {
        return $this->Title;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->Iri = $iri;
        return $this;
    }

    public function setTitle(?string $title): self {
        $this->Title = $title;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'related_object');

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
