<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Exceptions\InconsistentValues;
use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

class AttributedAgent implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?Person $person = null;
    public protected(set) ?Organization $organization = null;

    public function setPerson(?Person $person): self
    {
        if ($this->organization && $person) {
            throw new InconsistentValues('either person or organisation can be set only');
        }
        $this->person = $person;

        return $this;
    }

    public function setOrganization(?Organization $organization): self
    {
        if ($this->person && $organization) {
            throw new InconsistentValues('either person or organisation can be set only');
        }
        $this->organization = $organization;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'attributed_agent');

        if ($this->person) {
            $this->appendChildIfNotNull($element, $this->person);
        }
        if ($this->organization) {
            $this->appendChildIfNotNull($element, $this->organization);
        }

        return $element;
    }
}
