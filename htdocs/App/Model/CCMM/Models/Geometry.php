<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a geometry with GML and WKT representations.
 */
class Geometry implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $gml = null;
    public protected(set) ?Wkt $wkt = null;

    public function __construct()
    {
    }

    // Getters
    public function getGml(): ?string
    {
        return $this->gml;
    }

    public function getWkt(): ?Wkt
    {
        return $this->wkt;
    }

    // Setters
    public function setGml(?string $gml): self
    {
        $this->gml = $gml;

        return $this;
    }

    public function setWkt(?Wkt $wkt): self
    {
        $this->wkt = $wkt;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'geometry');

        // Note: GML content would need to be parsed and added as XML elements
        // For simplicity, we're treating it as a string here
        if (null !== $this->getGml()) {
            // This is a simplified approach - in a real implementation,
            // you would parse the GML and add it as XML elements
            $gmlFragment = $document->createDocumentFragment();
            $gmlFragment->appendXML($this->getGml());
            $element->appendChild($gmlFragment);
        }

        $this->appendChildIfNotNull($element, $this->getWkt());

        return $element;
    }
}
