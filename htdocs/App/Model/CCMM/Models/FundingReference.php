<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a funding reference with identifier, title, program and funder.
 */
class FundingReference implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) ?string $localIdentifier = null;
    public protected(set) ?string $awardTitle = null;
    public protected(set) ?string $fundingProgram = null;
    public protected(set) ?Funder $funder = null;

    public function getIri(): ?string
    {
        return $this->iri;
    }

    public function getLocalIdentifier(): ?string
    {
        return $this->localIdentifier;
    }

    public function getAwardTitle(): ?string
    {
        return $this->awardTitle;
    }

    public function getFundingProgram(): ?string
    {
        return $this->fundingProgram;
    }

    public function getFunder(): ?Funder
    {
        return $this->funder;
    }

    // Setters
    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function setLocalIdentifier(?string $localIdentifier): self
    {
        $this->localIdentifier = $localIdentifier;

        return $this;
    }

    public function setAwardTitle(?string $awardTitle): self
    {
        $this->awardTitle = $awardTitle;

        return $this;
    }

    public function setFundingProgram(?string $fundingProgram): self
    {
        $this->fundingProgram = $fundingProgram;

        return $this;
    }

    public function setFunder(?Funder $funder): self
    {
        $this->funder = $funder;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'funding_reference');

        if (null !== $this->getIri()) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if (null !== $this->getLocalIdentifier()) {
            $localIdElement = $this->createElement($document, 'local_identifier', $this->getLocalIdentifier());
            $element->appendChild($localIdElement);
        }

        if (null !== $this->getAwardTitle()) {
            $titleElement = $this->createElement($document, 'award_title', $this->getAwardTitle());
            $element->appendChild($titleElement);
        }

        if (null !== $this->getFundingProgram()) {
            $programElement = $this->createElement($document, 'funding_program', $this->getFundingProgram());
            $element->appendChild($programElement);
        }

        $this->appendChildIfNotNull($element, $this->getFunder());

        return $element;
    }
}
