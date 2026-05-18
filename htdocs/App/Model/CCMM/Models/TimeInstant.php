<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a time instant with date/time and date type.
 */
class TimeInstant implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $dateTime = null;
    public protected(set) ?string $date = null;
    public protected(set) ?DateType $dateType = null;

    public function __construct()
    {
    }

    // Getters
    public function getDateTime(): ?string
    {
        return $this->DateTime;
    }

    public function getDate(): ?string
    {
        return $this->Date;
    }

    public function getDateType(): ?DateType
    {
        return $this->DateType;
    }

    // Setters
    public function setDateTime(?string $dateTime): self
    {
        $this->DateTime = $dateTime;

        return $this;
    }

    public function setDate(?string $date): self
    {
        $this->Date = $date;

        return $this;
    }

    public function setDateType(?DateType $dateType): self
    {
        $this->DateType = $dateType;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'time_instant');

        if (null !== $this->getDateTime()) {
            $dateTimeElement = $this->createElement($document, 'date_time', $this->getDateTime());
            $element->appendChild($dateTimeElement);
        }

        if (null !== $this->getDate()) {
            $dateElement = $this->createElement($document, 'date', $this->getDate());
            $element->appendChild($dateElement);
        }

        $this->appendChildIfNotNull($element, $this->getDateType(), 'date_type');

        return $element;
    }
}
