<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a provenance placeholder
 */
class Provenance implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?string $description = null;

    public function __construct() {
    }

    // Getters
    public function getDescription(): ?string {
        return $this->description;
    }

    // Setters
    public function setDescription(?string $description): self {
        $this->description = $description;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'provenance');

        if ($this->description !== null) {
            $element->textContent = $this->description;
        }

        return $element;
    }
}
