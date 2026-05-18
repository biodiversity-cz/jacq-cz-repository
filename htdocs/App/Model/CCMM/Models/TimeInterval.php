<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a time interval with beginning and end time instants and date type.
 */
class TimeInterval implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?TimeInstant $beginningTimeInstant = null;
    public protected(set) ?TimeInstant $endTimeInstant = null;
    public protected(set) ?DateType $dateType = null;

    public function __construct()
    {
    }

    // Getters
    public function getBeginningTimeInstant(): ?TimeInstant
    {
        return $this->beginningTimeInstant;
    }

    public function getEndTimeInstant(): ?TimeInstant
    {
        return $this->endTimeInstant;
    }

    public function getDateType(): ?DateType
    {
        return $this->dateType;
    }

    // Setters
    public function setBeginningTimeInstant(?TimeInstant $beginningTimeInstant): self
    {
        $this->beginningTimeInstant = $beginningTimeInstant;

        return $this;
    }

    public function setEndTimeInstant(?TimeInstant $endTimeInstant): self
    {
        $this->endTimeInstant = $endTimeInstant;

        return $this;
    }

    public function setDateType(?DateType $dateType): self
    {
        $this->dateType = $dateType;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'time_interval');

        if (null !== $this->getBeginningTimeInstant()) {
            $beginningElement = $this->getBeginningTimeInstant()->toXml($document, 'beginning_time_instant');
            $element->appendChild($beginningElement);
        }

        if (null !== $this->getEndTimeInstant()) {
            $endElement = $this->getEndTimeInstant()->toXml($document, 'end_time_instant');
            $element->appendChild($endElement);
        }

        $this->appendChildIfNotNull($element, $this->getDateType(), 'date_type');

        return $element;
    }
}
