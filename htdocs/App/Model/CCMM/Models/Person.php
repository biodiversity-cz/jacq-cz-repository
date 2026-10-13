<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a person with identifiers, names, contact info and affiliation.
 */
class Person implements XmlSerializable
{
    use XmlSerializableTrait;

    public function __construct(
        public protected(set) ?Identifier $identifier = null,
        public protected(set) ?string $name = null,
        public protected(set) ?string $givenName = null,
        public protected(set) ?string $familyName = null,
        public protected(set) ?ContactPoint $contactPoint = null,
        public protected(set) ?Organization $affiliation = null,
    ) {
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'person');

        $this->appendChildIfNotNull($element, $this->identifier);
        $this->appendChildIfNotNull($element, $this->contactPoint);
        $this->appendChildIfNotNull($element, $this->affiliation);

        if (null !== $this->name) {
            $nameElement = $this->createElement($document, 'name', $this->name);
            $element->appendChild($nameElement);
        }

        if (null !== $this->givenName) {
            $givenNameElement = $this->createElement($document, 'given_name', $this->givenName);
            $element->appendChild($givenNameElement);
        }

        if (null !== $this->familyName) {
            $familyNameElement = $this->createElement($document, 'family_name', $this->familyName);
            $element->appendChild($familyNameElement);
        }

        return $element;
    }
}
