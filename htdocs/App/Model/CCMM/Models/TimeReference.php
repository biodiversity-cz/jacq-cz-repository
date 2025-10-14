<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a time reference which can be either a time instant or time interval
 */
class TimeReference implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?TimeInstant $timeInstant = null;
    private ?TimeInterval $timeInterval = null;

    public function __construct() {
    }


    // Getters
    public function getTimeInstant(): ?TimeInstant {
        return $this->timeInstant;
    }

    public function getTimeInterval(): ?TimeInterval {
        return $this->timeInterval;
    }

    // Setters
    public function setTimeInstant(?TimeInstant $timeInstant): self {
        $this->timeInstant = $timeInstant;
        return $this;
    }

    public function setTimeInterval(?TimeInterval $timeInterval): self {
        $this->timeInterval = $timeInterval;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'time_reference');

        if ($this->getTimeInstant() !== null) {
            $timeInstantElement = $this->getTimeInstant()->toXml($document, 'time_instant');
            $element->appendChild($timeInstantElement);
        }

        if ($this->getTimeInterval() !== null) {
            $timeIntervalElement = $this->getTimeInterval()->toXml($document, 'time_interval');
            $element->appendChild($timeIntervalElement);
        }

        return $element;
    }
}
