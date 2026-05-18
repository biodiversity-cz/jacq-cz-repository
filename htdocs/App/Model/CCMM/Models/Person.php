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
        public ?Identifier $identifier = null,
        public ?string $name = null,
        public ?string $givenName = null,
        public ?string $familyName = null,
        public ?ContactPoint $contactPoint = null,
        public ?Organization $affiliation = null,
    ) {
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'person');

        $this->appendChildIfNotNull($element, $this->getIdentifier());
        $this->appendChildIfNotNull($element, $this->getContactPoint());
        $this->appendChildIfNotNull($element, $this->getAffiliation());

        if (null !== $this->name) {
            $nameElement = $this->createElement($document, 'name', $this->name);
            $element->appendChild($nameElement);
        }

        if (null !== $this->getGivenName()) {
            $givenNameElement = $this->createElement($document, 'given_name', $this->getGivenName());
            $element->appendChild($givenNameElement);
        }

        if (null !== $this->getFamilyName()) {
            $familyNameElement = $this->createElement($document, 'family_name', $this->getFamilyName());
            $element->appendChild($familyNameElement);
        }

        return $element;
    }
}
