<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a distribution data service
 */
class DistributionDataService implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $iri = null;
    private ?string $title = null;
    private ?AccessService $accessService = null;
    private ?Specification $specification = null;
    private ?Documentation $documentation = null;
    private ?string $description = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->Iri;
    }

    public function getTitle(): ?string {
        return $this->Title;
    }

    public function getAccessService(): ?AccessService {
        return $this->AccessService;
    }

    public function getSpecification(): ?Specification {
        return $this->Specification;
    }

    public function getDocumentation(): ?Documentation {
        return $this->Documentation;
    }

    public function getDescription(): ?string {
        return $this->Description;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->Iri = $iri;
        return $this;
    }

    public function setTitle(?string $title): self {
        $this->Title = $title;
        return $this;
    }

    public function setAccessService(?AccessService $accessService): self {
        $this->AccessService = $accessService;
        return $this;
    }

    public function setSpecification(?Specification $specification): self {
        $this->Specification = $specification;
        return $this;
    }

    public function setDocumentation(?Documentation $documentation): self {
        $this->Documentation = $documentation;
        return $this;
    }

    public function setDescription(?string $description): self {
        $this->Description = $description;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'distribution_data_service');

        if ($this->getIri() !== null) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if ($this->getTitle() !== null) {
            $titleElement = $this->createElement($document, 'title', $this->getTitle());
            $titleElement->setAttribute('xml:lang', 'cs');
            $element->appendChild($titleElement);
        }

        $this->appendChildIfNotNull($element, $this->getAccessService(), 'access_service');
        $this->appendChildIfNotNull($element, $this->getSpecification());
        $this->appendChildIfNotNull($element, $this->getDocumentation());

        if ($this->getDescription() !== null) {
            $descElement = $this->createElement($document, 'description', $this->getDescription());
            $descElement->setAttribute('xml:lang', 'cs');
            $element->appendChild($descElement);
        }

        return $element;
    }
}
