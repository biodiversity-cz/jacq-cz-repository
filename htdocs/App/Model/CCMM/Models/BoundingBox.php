<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a bounding box with lower and upper corners.
 */
class BoundingBox implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $lowerCorner = null;
    public protected(set) ?string $upperCorner = null;

    public function __construct()
    {
    }

    // Getters
    public function getLowerCorner(): ?string
    {
        return $this->lowerCorner;
    }

    public function getUpperCorner(): ?string
    {
        return $this->upperCorner;
    }

    // Setters
    public function setLowerCorner(?string $lowerCorner): self
    {
        $this->lowerCorner = $lowerCorner;

        return $this;
    }

    public function setUpperCorner(?string $upperCorner): self
    {
        $this->upperCorner = $upperCorner;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'bounding_box');

        if (null !== $this->getLowerCorner()) {
            $lowerElement = $this->createElement($document, 'gml:lowerCorner', $this->getLowerCorner());
            $element->appendChild($lowerElement);
        }

        if (null !== $this->getUpperCorner()) {
            $upperElement = $this->createElement($document, 'gml:upperCorner', $this->getUpperCorner());
            $element->appendChild($upperElement);
        }

        return $element;
    }
}
