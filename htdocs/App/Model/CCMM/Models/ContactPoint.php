<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a contact point with email, phone and address
 */
class ContactPoint implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $email = null;
    private ?string $phone = null;
    private ?Address $address = null;

    public function __construct() {
    }


    // Getters
    public function getEmail(): ?string {
        return $this->email;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function getAddress(): ?Address {
        return $this->address;
    }

    // Setters
    public function setEmail(?string $email): self {
        $this->email = $email;
        return $this;
    }

    public function setPhone(?string $phone): self {
        $this->phone = $phone;
        return $this;
    }

    public function setAddress(?Address $address): self {
        $this->address = $address;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'contact_point');

        if ($this->getEmail() !== null) {
            $emailElement = $this->createElement($document, 'email', $this->getEmail());
            $element->appendChild($emailElement);
        }

        if ($this->getPhone() !== null) {
            $phoneElement = $this->createElement($document, 'phone', $this->getPhone());
            $element->appendChild($phoneElement);
        }

        $this->appendChildIfNotNull($element, $this->getAddress());

        return $element;
    }
}
