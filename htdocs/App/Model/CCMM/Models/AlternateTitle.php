<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents an alternate title with title and alternate title type
 */
class AlternateTitle implements XmlSerializable
{
    use XmlSerializableTrait;

    private Title $title;
    private ?AlternateTitleType $alternateTitleType = null;

    public function __construct() {
    }


    // Getters
    public function getTitle(): Title {
        return $this->title;
    }

    public function getAlternateTitleType(): ?AlternateTitleType {
        return $this->alternateTitleType;
    }

    // Setters
    public function setTitle(Title $title): self {
        $this->title = $title;
        return $this;
    }

    public function setAlternateTitleType(?AlternateTitleType $alternateTitleType): self {
        $this->alternateTitleType = $alternateTitleType;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'alternate_title');

        $titleElement = $this->getTitle()->toXml($document);
        $element->appendChild($titleElement);

        $this->appendChildIfNotNull($element, $this->getAlternateTitleType(), 'alternate_title_type');

        return $element;
    }
}
