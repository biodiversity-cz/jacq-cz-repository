<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a subject with IRI, title, classification code and subject scheme.
 */
class Subject implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) ?string $classificationCode = null;

    public function __construct(public protected(set) SubjectScheme $subjectScheme)
    {
    }

    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function setClassificationCode(?string $classificationCode): self
    {
        $this->classificationCode = $classificationCode;

        return $this;
    }


    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'subject');

        if (null !== $this->iri) {
            $iriElement = $this->createElement($document, 'iri', $this->iri);
            $element->appendChild($iriElement);
        }

        if (null !== $this->classificationCode) {
            $codeElement = $this->createElement($document, 'classification_code', $this->classificationCode);
            $element->appendChild($codeElement);
        }

        $element->appendChild($this->subjectScheme->toXml($document));


        return $element;
    }
}
