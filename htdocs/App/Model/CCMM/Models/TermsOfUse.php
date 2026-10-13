<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents terms of use with access rights, license, description and contact point.
 */
class TermsOfUse implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?Rights $accessRights = null;
    public protected(set) ?Licence $license = null;
    public protected(set) ?string $description = null;
    public protected(set) ?ContactPoint $contactPoint = null;

    public function setAccessRights(?Rights $accessRights): self
    {
        $this->accessRights = $accessRights;

        return $this;
    }

    public function setLicense(?Licence $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setContactPoint(?ContactPoint $contactPoint): self
    {
        $this->contactPoint = $contactPoint;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'terms_of_use');

        $this->appendChildIfNotNull($element, $this->accessRights, 'access_rights');
        $this->appendChildIfNotNull($element, $this->license);
        $this->appendChildIfNotNull($element, $this->contactPoint);

        if (null !== $this->description) {
            $descElement = $this->createElement($document, 'description', $this->description);
            $descElement->setAttribute('xml:lang', 'cs');
            $element->appendChild($descElement);
        }

        return $element;
    }
}
