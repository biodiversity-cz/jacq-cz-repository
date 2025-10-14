<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a funder which is an organization
 */
class Funder implements XmlSerializable
{
    use XmlSerializableTrait;

    private Organization $organization;

    public function __construct() {
    }


    // Getters
    public function getOrganization(): Organization {
        return $this->organization;
    }

    // Setters
    public function setOrganization(Organization $organization): self {
        $this->organization = $organization;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'funder');
        $orgElement = $this->getOrganization()->toXml($document);
        $element->appendChild($orgElement);
        return $element;
    }
}
