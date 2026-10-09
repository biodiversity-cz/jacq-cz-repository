<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models\Base;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a media type with IRI and label.
 */
abstract class IriBase implements XmlSerializable
{
    use XmlSerializableTrait;

    public function __construct(public protected(set) string $iri)
    {
    }

    abstract public static function elementName(): string;


    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? static::elementName());

        $iriElement = $this->createElement($document, 'iri', $this->iri);
        $element->appendChild($iriElement);

        return $element;
    }
}
