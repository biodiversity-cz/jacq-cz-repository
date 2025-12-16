<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents an alternate title type with IRI and labels
 */
class AlternateTitleType implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * @param Title[] $labels
     */
    public function __construct(
        protected(set) ?string $iri = null,
        protected(set) array $labels = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'alternate_title_type');

        if ($this->iri !== null) {
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
