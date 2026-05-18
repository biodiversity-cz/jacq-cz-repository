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
    public protected(set) ?Title $title = null;
    public protected(set) ?string $classificationCode = null;
    public protected(set) ?SubjectScheme $subjectScheme = null;

    public function __construct()
    {
    }

    // Getters
    public function getIri(): ?string
    {
        return $this->iri;
    }

    public function getTitle(): ?Title
    {
        return $this->title;
    }

    public function getClassificationCode(): ?string
    {
        return $this->classificationCode;
    }

    public function getSubjectScheme(): ?SubjectScheme
    {
        return $this->subjectScheme;
    }

    // Setters
    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function setTitle(?Title $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setClassificationCode(?string $classificationCode): self
    {
        $this->classificationCode = $classificationCode;

        return $this;
    }

    public function setSubjectScheme(?SubjectScheme $subjectScheme): self
    {
        $this->subjectScheme = $subjectScheme;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'subject');

        if (null !== $this->getIri()) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if (null !== $this->getTitle()) {
            $titleElement = $this->getTitle()->toXml($document);
            $element->appendChild($titleElement);
        }

        if (null !== $this->getClassificationCode()) {
            $codeElement = $this->createElement($document, 'classification_code', $this->getClassificationCode());
            $element->appendChild($codeElement);
        }

        $this->appendChildIfNotNull($element, $this->getSubjectScheme(), 'subject_scheme');

        return $element;
    }
}
