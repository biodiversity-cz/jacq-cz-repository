<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\Enum\Language;

/**
 * Represents a distribution data service
 */
class DistributionDataService implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $iri = null;
    private array $titles = [];
    private ?AccessService $accessService = null;
    private ?Specification $specification = null;
    private ?Documentation $documentation = null;
    private array $descriptions = [];

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->iri;
    }

    public function getAccessService(): ?AccessService {
        return $this->accessService;
    }

    public function getSpecification(): ?Specification {
        return $this->specification;
    }

    public function getDocumentation(): ?Documentation {
        return $this->documentation;
    }

    public function getTitles(): array {
        return $this->titles;
    }

    public function getDescriptions(): array {
        return $this->descriptions;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->iri = $iri;
        return $this;
    }

    public function addTitle(string $title, Language $lang = Language::CS): self {
        $this->titles[$lang->value] = $title;
        return $this;
    }

    public function setAccessService(?AccessService $accessService): self {
        $this->accessService = $accessService;
        return $this;
    }

    public function setSpecification(?Specification $specification): self {
        $this->specification = $specification;
        return $this;
    }

    public function setDocumentation(?Documentation $documentation): self {
        $this->documentation = $documentation;
        return $this;
    }

    public function addDescription(string $description, Language $lang = Language::CS): self {
        $this->descriptions[$lang->value] = $description;
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

        if (!empty($this->getTitles())) {
            foreach ($this->getTitles() as $lang => $text) {
                $titleElement = $this->createElement($document, 'title', $text);
                $titleElement->setAttribute('xml:lang', $lang);
                $element->appendChild($titleElement);
            }
        }

        $this->appendChildIfNotNull($element, $this->getAccessService(), 'access_service');
        $this->appendChildIfNotNull($element, $this->getSpecification());
        $this->appendChildIfNotNull($element, $this->getDocumentation());

        if (!empty($this->getDescriptions())) {
            foreach ($this->getDescriptions() as $lang => $text) {
                $descElement = $this->createElement($document, 'description', $text);
                $descElement->setAttribute('xml:lang', $lang);
                $element->appendChild($descElement);
            }
        }

        return $element;
    }
}
