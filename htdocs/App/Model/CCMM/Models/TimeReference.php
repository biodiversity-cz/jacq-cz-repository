<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a time reference which can be either a time instant or time interval.
 */
class TimeReference implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * for easy flow the temporal_representation is not modeled and created inside both time variants manually.
     */
    public protected(set) ?TimeInstant $timeInstant = null;
    public protected(set) ?TimeInterval $timeInterval = null;

    public protected(set) ?DateType $dateType = null;

    public function setTimeInstant(?TimeInstant $timeInstant): self
    {
        $this->timeInstant = $timeInstant;

        return $this;
    }

    public function setTimeInterval(?TimeInterval $timeInterval): self
    {
        $this->timeInterval = $timeInterval;

        return $this;
    }

    public function setDateType(?DateType $dateType): TimeReference
    {
        $this->dateType = $dateType;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'time_reference');

        if (null !== $this->timeInstant) {
            $timeInstantElement = $this->timeInstant->toXml($document);
            $element->appendChild($timeInstantElement);
        }

        if (null !== $this->timeInterval) {
            $timeIntervalElement = $this->timeInterval->toXml($document);
            $element->appendChild($timeIntervalElement);
        }

        if (null !== $this->dateType) {
            $dateTypeElement = $this->dateType->toXml($document);
            $element->appendChild($dateTypeElement);
        }

        return $element;
    }
}
