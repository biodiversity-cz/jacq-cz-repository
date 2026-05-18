<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a provenance placeholder.
 */
class Provenance implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $description = null;

    public function __construct()
    {
    }

    // Getters
    public function getDescription(): ?string
    {
        return $this->description;
    }

    // Setters
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'provenance');

        if (null !== $this->description) {
            $element->textContent = $this->description;
        }

        return $element;
    }
}
