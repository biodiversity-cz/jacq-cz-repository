<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a download URL with IRI and labels
 */
class DownloadUrl implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * @param Title[] $labels
     */
    public function __construct(
        public ?string $iri = null,
        public array $labels = []
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'download_url');

        if ($this->getIri() !== null) {
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
