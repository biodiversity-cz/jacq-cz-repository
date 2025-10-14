<?php

declare(strict_types=1);

namespace App\Model\CCMM;

/**
 * Interface for objects that can be serialized to XML
 */
interface XmlSerializable
{
    /**
     * Serialize the object to XML
     *
     * @param \DOMDocument $document The DOM document to append to
     * @param string|null $elementName Optional element name, if different from default
     * @return \DOMElement The created XML element
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement;
}
