<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a contact point with email, phone and address.
 */
class ContactPoint implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $email = null;
    public protected(set) ?string $phone = null;
    public protected(set) ?Address $address = null;

    public function __construct()
    {
    }

    // Getters
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    // Setters
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'contact_point');

        if (null !== $this->email) {
            $emailElement = $this->createElement($document, 'email', $this->email);
            $element->appendChild($emailElement);
        }

        if (null !== $this->getPhone()) {
            $phoneElement = $this->createElement($document, 'phone', $this->getPhone());
            $element->appendChild($phoneElement);
        }

        $this->appendChildIfNotNull($element, $this->getAddress());

        return $element;
    }
}
