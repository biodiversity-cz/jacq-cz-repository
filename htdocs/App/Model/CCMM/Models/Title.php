<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a title with optional language attribute
 */
class Title implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) string $title;
    protected(set) ?string $language = null;

    public function __construct() {
    }

    // Getters
    public function getTitle(): string {
        return $this->title;
    }

    public function getLanguage(): ?string {
        return $this->language;
    }

    // Setters
    public function setTitle(string $title): self {
        $this->title = $title;
        return $this;
    }

    public function setLanguage(?string $language): self {
        $this->language = $language;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'title', $this->getTitle());

        if ($this->getLanguage() !== null) {
            $element->setAttribute('xml:lang', $this->getLanguage());
        }

        return $element;
    }
}
