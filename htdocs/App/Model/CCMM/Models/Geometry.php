<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a geometry with WKT representation. GML is also present in CCMM, but it is not handled by NMA, therefore, ignored.
 */
class Geometry implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?Wkt $wkt = null;

    public function __construct()
    {
    }

    public function getWkt(): ?Wkt
    {
        return $this->wkt;
    }

    public function setWkt(?Wkt $wkt): self
    {
        $this->wkt = $wkt;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'geometry');

        $this->appendChildIfNotNull($element, $this->getWkt());

        return $element;
    }
}
