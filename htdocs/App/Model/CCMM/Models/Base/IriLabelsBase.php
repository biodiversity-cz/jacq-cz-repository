<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models\Base;

use App\Model\CCMM\Enum\Language;
use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a media type with IRI and label.
 */
abstract class IriLabelsBase implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) array $labels = [];

    abstract public static function elementName(): string;

    public function getIri(): ?string
    {
        return $this->iri;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function addLabel(string $label, Language $lang = Language::CS): self
    {
        $this->labels[$lang->value] = $label;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? static::elementName());

        if (null !== $this->getIri()) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if (!empty($this->getLabels())) {
            foreach ($this->getLabels() as $lang => $text) {
                $titleElement = $this->createElement($document, 'label', $text);
                $titleElement->setAttribute('xml:lang', $lang);
                $element->appendChild($titleElement);
            }
        }

        return $element;
    }
}
