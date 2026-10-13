<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a qualified relation with a relation (person or organization) and role.
 */
class QualifiedAttribution implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?Role $role = null;
    public protected(set) ?AttributedAgent $attributedAgent = null;

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function setAttributedAgent(?AttributedAgent $attributedAgent): self
    {
        $this->attributedAgent = $attributedAgent;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'qualified_attribution');

        $this->appendChildIfNotNull($element, $this->attributedAgent);
        $this->appendChildIfNotNull($element, $this->role);

        return $element;
    }
}
