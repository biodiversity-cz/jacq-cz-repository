<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a location with bounding box, name, geometry and related objects
 */
class Location implements XmlSerializable
{
    use XmlSerializableTrait;

    /**
     * @param RelatedObject[] $relatedObjects
     */
    public function __construct(
        public ?BoundingBox $boundingBox = null,
        public ?string $name = null,
        public ?Geometry $geometry = null,
        public array $relatedObjects = [],
        public ?RelationType $relationType = null
    ) {
    }

    /**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'location');

        $this->appendChildIfNotNull($element, $this->getBoundingBox(), 'bounding_box');
        $this->appendChildIfNotNull($element, $this->getGeometry());
        $this->appendChildIfNotNull($element, $this->getRelationType(), 'relation_type');

        if ($this->getName() !== null) {
            $nameElement = $this->createElement($document, 'name', $this->getName());
            $element->appendChild($nameElement);
        }

        foreach ($this->getRelatedObjects() as $relatedObject) {
            $relatedElement = $relatedObject->toXml($document, 'related_object');
            $element->appendChild($relatedElement);
        }

        return $element;
    }
}
