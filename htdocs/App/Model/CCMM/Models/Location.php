<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a location with bounding box, name, geometry and related objects.
 */
class Location implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * @param RelatedObject[] $relatedObjects
     */
    public function __construct(
        protected(set) ?BoundingBox $boundingBox = null,
        protected(set) ?string $name = null,
        protected(set) ?Geometry $geometry = null,
        protected(set) array $relatedObjects = [],
        protected(set) ?RelationType $relationType = null,
    ) {
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'location');

        $this->appendChildIfNotNull($element, $this->getBoundingBox(), 'bounding_box');
        $this->appendChildIfNotNull($element, $this->getGeometry());
        $this->appendChildIfNotNull($element, $this->getRelationType(), 'relation_type');

        if (null !== $this->name) {
            $nameElement = $this->createElement($document, 'name', $this->name);
            $element->appendChild($nameElement);
        }

        foreach ($this->getRelatedObjects() as $relatedObject) {
            $relatedElement = $relatedObject->toXml($document, 'related_object');
            $element->appendChild($relatedElement);
        }

        return $element;
    }
}
