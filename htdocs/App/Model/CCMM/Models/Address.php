<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents an address with full address text
 */
class Address implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $fullAddress = null;

    public function __construct() {
    }


    // Getters
    public function getFullAddress(): ?string {
        return $this->fullAddress;
    }

    // Setters
    public function setFullAddress(?string $fullAddress): self {
        $this->fullAddress = $fullAddress;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'address');

        if ($this->getFullAddress() !== null) {
            $fullAddressElement = $this->createElement($document, 'full_address', $this->getFullAddress());
            $element->appendChild($fullAddressElement);
        }

        return $element;
    }
}
