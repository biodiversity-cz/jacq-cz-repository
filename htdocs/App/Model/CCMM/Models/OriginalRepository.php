<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Enum\Language;
use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents an original repository with IRI.
 */
class OriginalRepository extends IriLabelsBase {

    public protected(set) ?string $iri = null;
    public protected(set) array $labels = [];
    public protected(set) string $description;
    public protected(set)  array $qualifiedAttributions = [];
    public static function elementName(): string
    {
        return 'original_repository';
    }

    public function setDescription(string $description): OriginalRepository
    {
        $this->description = $description;
        return $this;
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

    public function setQualifiedAttributions(array $qualifiedAttributions): OriginalRepository
    {
        $this->qualifiedAttributions = $qualifiedAttributions;
        return $this;
    }


    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? static::elementName());

        if (null !== $this->iri) {
            $iriElement = $this->createElement($document, 'iri', $this->iri);
            $element->appendChild($iriElement);
        }

        if (!empty($this->labels)) {
            foreach ($this->labels as $lang => $text) {
                $titleElement = $this->createElement($document, 'label', $text);
                $titleElement->setAttribute('xml:lang', $lang);
                $element->appendChild($titleElement);
            }
        }
        $descriptionElement = $this->createElement($document, 'description');
        $descriptionElement->setAttribute('xml:lang', Language::EN->value);
        $element->appendChild($descriptionElement);

        foreach ($this->qualifiedAttributions as $qualifiedRelation) {
            $qualifiedRelationElement = $qualifiedRelation->toXml($document);
            $element->appendChild($qualifiedRelationElement);
        }

        return $element;
    }
}
