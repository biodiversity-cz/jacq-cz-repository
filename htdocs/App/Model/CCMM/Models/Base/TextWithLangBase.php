<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models\Base;

use App\Model\CCMM\Enum\Language;
use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

abstract class TextWithLangBase implements XmlSerializable
{
    use XmlSerializableTrait;

    public function __construct(public protected(set) string $text, public protected(set) Language $language)
    {
    }

    abstract public static function elementName(): string;

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? static::elementName());
        $element->setAttribute('xml:lang', $this->language->value);

        return $element;
    }
}
