<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a qualified relation with a relation (person or organization) and role
 */
class QualifiedRelation implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?Role $role = null;
    protected(set) ?XmlSerializable $relation = null;

    public function __construct() {
    }


    // Getters
    public function getRole(): ?Role {
        return $this->role;
    }

    public function getRelation(): ?XmlSerializable {
        return $this->relation;
    }

    // Setters
    public function setRole(?Role $role): self {
        $this->role = $role;
        return $this;
    }

    public function setRelation(?XmlSerializable $relation): self {
        $this->relation = $relation;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'qualified_relation');

        if ($this->relation !== null) {
            $relationElement = $document->createElement('relation');
            $relationElement->appendChild($this->relation->toXml($document));
            $element->appendChild($relationElement);
        }

        $this->appendChildIfNotNull($element, $this->getRole());

        return $element;
    }
}
