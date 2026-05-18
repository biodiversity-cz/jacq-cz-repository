<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a checksum with value and algorithm.
 */
class Checksum implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $checksumValue = null;
    public protected(set) ?string $algorithm = null;

    public function __construct()
    {
    }

    // Getters
    public function getChecksumValue(): ?string
    {
        return $this->checksumValue;
    }

    public function getAlgorithm(): ?string
    {
        return $this->algorithm;
    }

    // Setters
    public function setChecksumValue(?string $checksumValue): self
    {
        $this->checksumValue = $checksumValue;

        return $this;
    }

    public function setAlgorithm(?string $algorithm): self
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'checksum');

        if (null !== $this->getChecksumValue()) {
            $valueElement = $this->createElement($document, 'checksum_value', $this->getChecksumValue());
            $element->appendChild($valueElement);
        }

        if (null !== $this->getAlgorithm()) {
            $algorithmElement = $this->createElement($document, 'algorithm', $this->getAlgorithm());
            $element->appendChild($algorithmElement);
        }

        return $element;
    }
}
