<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents an access service with IRI and endpoint URL
 */
class AccessService implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $iri = null;
    private ?EndpointUrl $endpointUrl = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->iri;
    }

    public function getEndpointUrl(): ?EndpointUrl {
        return $this->endpointUrl;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->iri = $iri;
        return $this;
    }

    public function setEndpointUrl(?EndpointUrl $endpointUrl): self {
        $this->endpointUrl = $endpointUrl;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'access_service');

        if ($this->getIri() !== null) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        $this->appendChildIfNotNull($element, $this->getEndpointUrl(), 'endpoint_url');

        return $element;
    }
}
