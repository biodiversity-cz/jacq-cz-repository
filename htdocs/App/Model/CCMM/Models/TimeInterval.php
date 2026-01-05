<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a time interval with beginning and end time instants and date type
 */
class TimeInterval implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?TimeInstant $beginningTimeInstant = null;
    protected(set) ?TimeInstant $endTimeInstant = null;
    protected(set) ?DateType $dateType = null;

    public function __construct() {
    }


    // Getters
    public function getBeginningTimeInstant(): ?TimeInstant {
        return $this->beginningTimeInstant;
    }

    public function getEndTimeInstant(): ?TimeInstant {
        return $this->endTimeInstant;
    }

    public function getDateType(): ?DateType {
        return $this->dateType;
    }

    // Setters
    public function setBeginningTimeInstant(?TimeInstant $beginningTimeInstant): self {
        $this->beginningTimeInstant = $beginningTimeInstant;
        return $this;
    }

    public function setEndTimeInstant(?TimeInstant $endTimeInstant): self {
        $this->endTimeInstant = $endTimeInstant;
        return $this;
    }

    public function setDateType(?DateType $dateType): self {
        $this->dateType = $dateType;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'time_interval');

        if ($this->getBeginningTimeInstant() !== null) {
            $beginningElement = $this->getBeginningTimeInstant()->toXml($document, 'beginning_time_instant');
            $element->appendChild($beginningElement);
        }

        if ($this->getEndTimeInstant() !== null) {
            $endElement = $this->getEndTimeInstant()->toXml($document, 'end_time_instant');
            $element->appendChild($endElement);
        }

        $this->appendChildIfNotNull($element, $this->getDateType(), 'date_type');

        return $element;
    }
}
