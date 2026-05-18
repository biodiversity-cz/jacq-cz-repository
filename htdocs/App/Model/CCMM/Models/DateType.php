<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a date type with IRI and labels.
 */
class DateType implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * @param Title[] $labels
     */
    public function __construct(
        public ?string $iri = null,
        public array $labels = [],
    ) {
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'date_type');

        if (null !== $this->getIri()) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        foreach ($this->getLabels() as $label) {
            $labelElement = $label->toXml($document, 'label');
            $element->appendChild($labelElement);
        }

        return $element;
    }
}
