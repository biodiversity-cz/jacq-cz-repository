<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a subject with IRI, title, classification code and subject scheme
 */
class Subject implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $iri = null;
    private ?Title $title = null;
    private ?string $classificationCode = null;
    private ?SubjectScheme $subjectScheme = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->iri;
    }

    public function getTitle(): ?Title {
        return $this->title;
    }

    public function getClassificationCode(): ?string {
        return $this->classificationCode;
    }

    public function getSubjectScheme(): ?SubjectScheme {
        return $this->subjectScheme;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->iri = $iri;
        return $this;
    }

    public function setTitle(?Title $title): self {
        $this->title = $title;
        return $this;
    }

    public function setClassificationCode(?string $classificationCode): self {
        $this->classificationCode = $classificationCode;
        return $this;
    }

    public function setSubjectScheme(?SubjectScheme $subjectScheme): self {
        $this->subjectScheme = $subjectScheme;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'subject');

        if ($this->getIri() !== null) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if ($this->getTitle() !== null) {
            $titleElement = $this->getTitle()->toXml($document);
            $element->appendChild($titleElement);
        }

        if ($this->getClassificationCode() !== null) {
            $codeElement = $this->createElement($document, 'classification_code', $this->getClassificationCode());
            $element->appendChild($codeElement);
        }

        $this->appendChildIfNotNull($element, $this->getSubjectScheme(), 'subject_scheme');

        return $element;
    }
}
