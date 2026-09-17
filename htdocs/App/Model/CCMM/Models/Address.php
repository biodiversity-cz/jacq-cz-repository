<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents an address with full address text.
 */
class Address implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $fullAddress = null;

    public function setFullAddress(?string $fullAddress): self
    {
        $this->fullAddress = $fullAddress;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'address');

        if (null !== $this->fullAddress) {
            $fullAddressElement = $this->createElement($document, 'full_address', $this->fullAddress);
            $element->appendChild($fullAddressElement);
        }

        return $element;
    }
}
