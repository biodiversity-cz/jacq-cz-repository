<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a time instant with date/time and date type
 */
class TimeInstant implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $dateTime = null;
    private ?string $date = null;
    private ?DateType $dateType = null;

    public function __construct() {
    }


    // Getters
    public function getDateTime(): ?string {
        return $this->DateTime;
    }

    public function getDate(): ?string {
        return $this->Date;
    }

    public function getDateType(): ?DateType {
        return $this->DateType;
    }

    // Setters
    public function setDateTime(?string $dateTime): self {
        $this->DateTime = $dateTime;
        return $this;
    }

    public function setDate(?string $date): self {
        $this->Date = $date;
        return $this;
    }

    public function setDateType(?DateType $dateType): self {
        $this->DateType = $dateType;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'time_instant');

        if ($this->getDateTime() !== null) {
            $dateTimeElement = $this->createElement($document, 'date_time', $this->getDateTime());
            $element->appendChild($dateTimeElement);
        }

        if ($this->getDate() !== null) {
            $dateElement = $this->createElement($document, 'date', $this->getDate());
            $element->appendChild($dateElement);
        }

        $this->appendChildIfNotNull($element, $this->getDateType(), 'date_type');

        return $element;
    }
}
