<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a validation result placeholder
 */
class ValidationResult implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?string $result = null;

    public function __construct() {
    }

    // Getters
    public function getResult(): ?string {
        return $this->result;
    }

    // Setters
    public function setResult(?string $result): self {
        $this->result = $result;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'validation_result');

        if ($this->getResult() !== null) {
            $element->textContent = $this->getResult();
        }

        return $element;
    }
}
