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

    public protected(set) ?\DateTime $dateTime = null;

    public function __construct()
    {
    }

    public function getDateTime(): ?\DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(?\DateTime $dateTime): self
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $parentElement = $this->createElement($document, $elementName ?? 'temporal_representation');
        $element = $this->createElement($document, $elementName ?? 'time_instant');

        if (null !== $this->getDateTime()) {
            $dateTimeElement = $this->createElement($document, 'date_time', $this->getDateTime()->format(DATE_ATOM));
            $element->appendChild($dateTimeElement);
        }

        $parentElement->appendChild($element);

        return $parentElement;
    }
}
