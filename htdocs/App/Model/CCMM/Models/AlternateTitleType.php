<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents an alternate title type with IRI and labels.
 */
class AlternateTitleType implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * @param Title[] $labels
     */
    public function __construct(
        public protected(set) ?string $iri = null,
        public protected(set) array $labels = [],
    ) {
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'alternate_title_type');

        if (null !== $this->iri) {
            $iriElement = $this->createElement($document, 'iri', $this->iri);
            $element->appendChild($iriElement);
        }

        foreach ($this->labels as $label) {
            $labelElement = $label->toXml($document, 'label');
            $element->appendChild($labelElement);
        }

        return $element;
    }
}
