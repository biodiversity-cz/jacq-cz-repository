<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a description with text and description type
 */
class Description implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?string $descriptionText = null;
    protected(set) ?DescriptionType $descriptionType = null;

    public function __construct() {
    }


    // Getters
    public function getDescriptionText(): ?string {
        return $this->descriptionText;
    }

    public function getDescriptionType(): ?DescriptionType {
        return $this->descriptionType;
    }

    // Setters
    public function setDescriptionText(?string $descriptionText): self {
        $this->descriptionText = $descriptionText;
        return $this;
    }

    public function setDescriptionType(?DescriptionType $descriptionType): self {
        $this->descriptionType = $descriptionType;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'description');

        if ($this->getDescriptionText() !== null) {
            $textElement = $this->createElement($document, 'description_text', $this->getDescriptionText());
            $element->appendChild($textElement);
        }

        $this->appendChildIfNotNull($element, $this->getDescriptionType(), 'description_type');

        return $element;
    }
}
