<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Enum\Language;
use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a title with language attribute.
 */
class Title implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) string $title;
    public protected(set) Language $language = Language::CS;

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'title', $this->title);
        $element->setAttribute('xml:lang', $this->language->value);

        return $element;
    }
}
