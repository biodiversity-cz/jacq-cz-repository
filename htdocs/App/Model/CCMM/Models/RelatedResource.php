<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a related resource with IRI, title, URL, type and relation type
 */
class RelatedResource implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $iri = null;
    private ?string $title = null;
    private ?string $resourceUrl = null;
    private ?ResourceType $resourceType = null;
    private ?ResourceRelationType $resourceRelationType = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->iri;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function getResourceUrl(): ?string {
        return $this->resourceUrl;
    }

    public function getResourceType(): ?ResourceType {
        return $this->resourceType;
    }

    public function getResourceRelationType(): ?ResourceRelationType {
        return $this->resourceRelationType;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->iri = $iri;
        return $this;
    }

    public function setTitle(?string $title): self {
        $this->title = $title;
        return $this;
    }

    public function setResourceUrl(?string $resourceUrl): self {
        $this->resourceUrl = $resourceUrl;
        return $this;
    }

    public function setResourceType(?ResourceType $resourceType): self {
        $this->resourceType = $resourceType;
        return $this;
    }

    public function setResourceRelationType(?ResourceRelationType $resourceRelationType): self {
        $this->resourceRelationType = $resourceRelationType;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'related_resource');

        if ($this->getIri() !== null) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if ($this->getTitle() !== null) {
            $titleElement = $this->createElement($document, 'title', $this->getTitle());
            $element->appendChild($titleElement);
        }

        if ($this->getResourceUrl() !== null) {
            $urlElement = $this->createElement($document, 'resource_url', $this->getResourceUrl());
            $element->appendChild($urlElement);
        }

        $this->appendChildIfNotNull($element, $this->getResourceType(), 'resource_type');
        $this->appendChildIfNotNull($element, $this->getResourceRelationType(), 'resource_relation_type');

        return $element;
    }
}
