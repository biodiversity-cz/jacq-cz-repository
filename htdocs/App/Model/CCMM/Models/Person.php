<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a person with identifiers, names, contact info and affiliation
 */
class Person implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * @param Identifier[] $identifiers
     */
    public function __construct(
        public ?Identifier $identifier = null,
        public ?string $name = null,
        public ?string $givenName = null,
        public ?string $familyName = null,
        public ?ContactPoint $contactPoint = null,
        public ?Organization $affiliation = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'person');

        $this->appendChildIfNotNull($element, $this->getIdentifier());
        $this->appendChildIfNotNull($element, $this->getContactPoint());
        $this->appendChildIfNotNull($element, $this->getAffiliation());

        if ($this->name !== null) {
            $nameElement = $this->createElement($document, 'name', $this->name);
            $element->appendChild($nameElement);
        }

        if ($this->getGivenName() !== null) {
            $givenNameElement = $this->createElement($document, 'given_name', $this->getGivenName());
            $element->appendChild($givenNameElement);
        }

        if ($this->getFamilyName() !== null) {
            $familyNameElement = $this->createElement($document, 'family_name', $this->getFamilyName());
            $element->appendChild($familyNameElement);
        }

        return $element;
    }
}
