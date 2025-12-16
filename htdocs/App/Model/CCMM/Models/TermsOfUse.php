<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents terms of use with access rights, license, description and contact point
 */
class TermsOfUse implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?AccessRights $accessRights = null;
    protected(set) ?License $license = null;
    protected(set) ?string $description = null;
    protected(set) ?ContactPoint $contactPoint = null;

    public function __construct() {
    }


    // Getters
    public function getAccessRights(): ?AccessRights {
        return $this->accessRights;
    }

    public function getLicense(): ?License {
        return $this->license;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function getContactPoint(): ?ContactPoint {
        return $this->contactPoint;
    }

    // Setters
    public function setAccessRights(?AccessRights $accessRights): self {
        $this->accessRights = $accessRights;
        return $this;
    }

    public function setLicense(?License $license): self {
        $this->license = $license;
        return $this;
    }

    public function setDescription(?string $description): self {
        $this->description = $description;
        return $this;
    }

    public function setContactPoint(?ContactPoint $contactPoint): self {
        $this->contactPoint = $contactPoint;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'terms_of_use');

        $this->appendChildIfNotNull($element, $this->getAccessRights(), 'access_rights');
        $this->appendChildIfNotNull($element, $this->license);
        $this->appendChildIfNotNull($element, $this->getContactPoint());

        if ($this->description !== null) {
            $descElement = $this->createElement($document, 'description', $this->description);
            $descElement->setAttribute('xml:lang', 'cs');
            $element->appendChild($descElement);
        }

        return $element;
    }
}
