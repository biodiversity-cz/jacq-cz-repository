<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a Well-Known Text (WKT) geometry representation
 */
class Wkt implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $wktText = null;
    private ?string $srsName = null;

    public function __construct() {
    }


    // Getters
    public function getWktText(): ?string {
        return $this->WktText;
    }

    public function getSrsName(): ?string {
        return $this->SrsName;
    }

    // Setters
    public function setWktText(?string $wktText): self {
        $this->WktText = $wktText;
        return $this;
    }

    public function setSrsName(?string $srsName): self {
        $this->SrsName = $srsName;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $attributes = [];
        if ($this->getSrsName() !== null) {
            $attributes['srsName'] = $this->getSrsName();
        }

        return $this->createElement($document, $elementName ?? 'wkt', $this->getWktText(), $attributes);
    }
}
