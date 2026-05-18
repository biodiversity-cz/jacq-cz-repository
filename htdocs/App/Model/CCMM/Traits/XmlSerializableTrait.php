<?php

declare(strict_types=1);

namespace App\Model\CCMM\Traits;

use App\Model\CCMM\XmlSerializable;

/**
 * Trait providing common XML serialization functionality.
 */
trait XmlSerializableTrait
{
    /**
     * Create a DOM element with optional attributes.
     *
     * @param \DOMDocument $document    The DOM document
     * @param string       $elementName The element name
     * @param string|null  $value       Optional text content
     * @param array        $attributes  Optional attributes
     *
     * @return \DOMElement The created element
     */
    protected function createElement(
        \DOMDocument $document,
        string $elementName,
        ?string $value = null,
        array $attributes = [],
    ): \DOMElement {
        $element = $document->createElement('ccmm:'.$elementName);

        if (null !== $value) {
            $element->textContent = $value;
        }

        foreach ($attributes as $name => $attrValue) {
            $element->setAttribute($name, $attrValue);
        }

        return $element;
    }

    /**
     * Append a child element if it's not null.
     *
     * @param \DOMElement          $parent      The parent element
     * @param XmlSerializable|null $child       The child object
     * @param string|null          $elementName Optional element name
     */
    protected function appendChildIfNotNull(
        \DOMElement $parent,
        ?XmlSerializable $child,
        ?string $elementName = null,
    ): void {
        if (null !== $child) {
            $parent->appendChild($child->toXml($parent->ownerDocument, $elementName));
        }
    }

    /**
     * Append multiple child elements.
     *
     * @param \DOMElement $parent      The parent element
     * @param array       $children    Array of XmlSerializable objects
     * @param string|null $elementName Optional element name
     */
    protected function appendChildElements(
        \DOMElement $parent,
        array $children,
        ?string $elementName = null,
    ): void {
        foreach ($children as $child) {
            if ($child instanceof XmlSerializable) {
                $parent->appendChild($child->toXml($parent->ownerDocument, $elementName));
            }
        }
    }
}
