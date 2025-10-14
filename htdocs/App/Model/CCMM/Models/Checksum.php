<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a checksum with value and algorithm
 */
class Checksum implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $checksumValue = null;
    private ?string $algorithm = null;

    public function __construct() {
    }


    // Getters
    public function getChecksumValue(): ?string {
        return $this->checksumValue;
    }

    public function getAlgorithm(): ?string {
        return $this->algorithm;
    }

    // Setters
    public function setChecksumValue(?string $checksumValue): self {
        $this->checksumValue = $checksumValue;
        return $this;
    }

    public function setAlgorithm(?string $algorithm): self {
        $this->algorithm = $algorithm;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'checksum');

        if ($this->getChecksumValue() !== null) {
            $valueElement = $this->createElement($document, 'checksum_value', $this->getChecksumValue());
            $element->appendChild($valueElement);
        }

        if ($this->getAlgorithm() !== null) {
            $algorithmElement = $this->createElement($document, 'algorithm', $this->getAlgorithm());
            $element->appendChild($algorithmElement);
        }

        return $element;
    }
}
