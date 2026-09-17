<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a dataset with all its metadata components.
 */
class Dataset implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) ?MetadataIdentification $metadataIdentification = null;
    public protected(set) array $identifiers = [];
    public protected(set) ?string $version = null;
    public protected(set) ?string $title = null;
    public protected(set) array $alternateTitles = [];
    public protected(set) array $qualifiedRelations = [];
    public protected(set) ?string $publicationYear = null;
    public protected(set) array $timeReferences = [];
    public protected(set) ?ResourceType $resourceType = null;
    public protected(set) ?string $primaryLanguage = null;
    public protected(set) array $otherLanguages = [];
    public protected(set) ?TermsOfUse $termsOfUse = null;
    public protected(set) array $subjects = [];
    public protected(set) array $descriptions = [];
    public protected(set) array $locations = [];
    public protected(set) array $fundingReferences = [];
    /**
     * the Funding structure is overcomplicated for herbaria purposes, allow storing XL fragment to create OAI-PMH in case of Funding.
     */
    public protected(set) ?string $fundingReferencesRaw = null;
    public protected(set) array $relatedResources = [];
    public protected(set) array $distributions = [];
    public protected(set) ?ValidationResult $validationResult = null;
    public protected(set) ?Provenance $provenance = null;

    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function setMetadataIdentification(?MetadataIdentification $metadataIdentification): self
    {
        $this->metadataIdentification = $metadataIdentification;

        return $this;
    }

    public function setIdentifiers(array $identifiers): self
    {
        $this->identifiers = $identifiers;

        return $this;
    }

    public function addIdentifier(Identifier $identifier): self
    {
        $this->identifiers[] = $identifier;

        return $this;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setAlternateTitles(array $alternateTitles): self
    {
        $this->alternateTitles = $alternateTitles;

        return $this;
    }

    public function addAlternateTitle(AlternateTitle $alternateTitle): self
    {
        $this->alternateTitles[] = $alternateTitle;

        return $this;
    }

    public function setQualifiedRelations(array $qualifiedRelations): self
    {
        $this->qualifiedRelations = $qualifiedRelations;

        return $this;
    }

    public function addQualifiedRelation(QualifiedRelation $qualifiedRelation): self
    {
        $this->qualifiedRelations[] = $qualifiedRelation;

        return $this;
    }

    public function setPublicationYear(?string $publicationYear): self
    {
        $this->publicationYear = $publicationYear;

        return $this;
    }

    public function setTimeReferences(array $timeReferences): self
    {
        $this->timeReferences = $timeReferences;

        return $this;
    }

    public function addTimeReference(TimeReference $timeReference): self
    {
        $this->timeReferences[] = $timeReference;

        return $this;
    }

    public function setResourceType(?ResourceType $resourceType): self
    {
        $this->resourceType = $resourceType;

        return $this;
    }

    public function setPrimaryLanguage(?string $primaryLanguage): self
    {
        $this->primaryLanguage = $primaryLanguage;

        return $this;
    }

    public function setOtherLanguages(array $otherLanguages): self
    {
        $this->otherLanguages = $otherLanguages;

        return $this;
    }

    public function addOtherLanguage(string $otherLanguage): self
    {
        $this->otherLanguages[] = $otherLanguage;

        return $this;
    }

    public function setTermsOfUse(?TermsOfUse $termsOfUse): self
    {
        $this->termsOfUse = $termsOfUse;

        return $this;
    }

    public function setSubjects(array $subjects): self
    {
        $this->subjects = $subjects;

        return $this;
    }

    public function addSubject(Subject $subject): self
    {
        $this->subjects[] = $subject;

        return $this;
    }

    public function setDescriptions(array $descriptions): self
    {
        $this->descriptions = $descriptions;

        return $this;
    }

    public function addDescription(Description $description): self
    {
        $this->descriptions[] = $description;

        return $this;
    }

    public function setLocations(array $locations): self
    {
        $this->locations = $locations;

        return $this;
    }

    public function addLocation(Location $location): self
    {
        $this->locations[] = $location;

        return $this;
    }

    public function setFundingReferences(array $fundingReferences): self
    {
        $this->fundingReferences = $fundingReferences;

        return $this;
    }

    public function addFundingReference(FundingReference $fundingReference): self
    {
        $this->fundingReferences[] = $fundingReference;

        return $this;
    }

    public function setRawFundingReference(?string $fundingReference): self
    {
        $this->fundingReferencesRaw = $fundingReference;

        return $this;
    }

    public function getRawFundingReferences(): ?string
    {
        return $this->fundingReferencesRaw;
    }

    public function setRelatedResources(array $relatedResources): self
    {
        $this->relatedResources = $relatedResources;

        return $this;
    }

    public function addRelatedResource(RelatedResource $relatedResource): self
    {
        $this->relatedResources[] = $relatedResource;

        return $this;
    }

    public function setDistributions(array $distributions): self
    {
        $this->distributions = $distributions;

        return $this;
    }

    public function addDistribution(Distribution $distribution): self
    {
        $this->distributions[] = $distribution;

        return $this;
    }

    public function setValidationResult(?ValidationResult $validationResult): self
    {
        $this->validationResult = $validationResult;

        return $this;
    }

    public function setProvenance(?Provenance $provenance): self
    {
        $this->provenance = $provenance;

        return $this;
    }

    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        // Create the root element with namespaces
        $element = $document->createElementNS('https://schema.ccmm.cz/research-data/1.0', 'ccmm:dataset');
        $element->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'https://schema.ccmm.cz/research-data/1.0 https://raw.githubusercontent.com/techlib/CCMM/refs/heads/main/dataset/schema.xsd');
        $element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gml', 'http://www.opengis.net/gml/3.2');
        $element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        if (null !== $this->iri) {
            $iriElement = $this->createElement($document, 'iri', $this->iri);
            $element->appendChild($iriElement);
        }

        $this->appendChildIfNotNull($element, $this->metadataIdentification, 'metadata_identification');

        foreach ($this->identifiers as $identifier) {
            $identifierElement = $identifier->toXml($document);
            $element->appendChild($identifierElement);
        }

        if (null !== $this->version) {
            $versionElement = $this->createElement($document, 'version', $this->version);
            $element->appendChild($versionElement);
        }

        if (null !== $this->title) {
            $titleElement = $this->createElement($document, 'title', $this->title);
            $element->appendChild($titleElement);
        }

        foreach ($this->alternateTitles as $alternateTitle) {
            $altTitleElement = $alternateTitle->toXml($document);
            $element->appendChild($altTitleElement);
        }

        foreach ($this->qualifiedRelations as $qualifiedRelation) {
            $qualifiedRelationElement = $qualifiedRelation->toXml($document);
            $element->appendChild($qualifiedRelationElement);
        }

        if (null !== $this->publicationYear) {
            $yearElement = $this->createElement($document, 'publication_year', $this->publicationYear);
            $element->appendChild($yearElement);
        }

        foreach ($this->timeReferences as $timeReference) {
            $timeRefElement = $timeReference->toXml($document);
            $element->appendChild($timeRefElement);
        }

        $this->appendChildIfNotNull($element, $this->resourceType, 'resource_type');

        if (null !== $this->primaryLanguage) {
            $primaryLangElement = $this->createElement($document, 'primary_language');
            $iriElement = $this->createElement($document, 'iri', $this->primaryLanguage);
            $primaryLangElement->appendChild($iriElement);
            $element->appendChild($primaryLangElement);
        }

        foreach ($this->otherLanguages as $otherLanguage) {
            $otherLangElement = $this->createElement($document, 'other_language');
            $iriElement = $this->createElement($document, 'iri', $otherLanguage);
            $otherLangElement->appendChild($iriElement);
            $element->appendChild($otherLangElement);
        }

        $this->appendChildIfNotNull($element, $this->termsOfUse, 'terms_of_use');

        foreach ($this->subjects as $subject) {
            $subjectElement = $subject->toXml($document);
            $element->appendChild($subjectElement);
        }

        foreach ($this->descriptions as $description) {
            $descriptionElement = $description->toXml($document);
            $element->appendChild($descriptionElement);
        }

        foreach ($this->locations as $location) {
            $locationElement = $location->toXml($document);
            $element->appendChild($locationElement);
        }

        foreach ($this->fundingReferences as $fundingReference) {
            $fundingElement = $fundingReference->toXml($document);
            $element->appendChild($fundingElement);
        }

        if (!empty($this->fundingReferencesRaw)) {
            $fragment = $document->createDocumentFragment();
            $fragment->appendXML($this->getRawFundingReferences());
            $element->appendChild($fragment);
        }

        foreach ($this->relatedResources as $relatedResource) {
            $relatedElement = $relatedResource->toXml($document);
            $element->appendChild($relatedElement);
        }

        foreach ($this->distributions as $distribution) {
            $distributionElement = $distribution->toXml($document);
            $element->appendChild($distributionElement);
        }

        // Add empty validation_result and provenance elements as placeholders
        if (null !== $this->validationResult) {
            $validationElement = $this->validationResult->toXml($document, 'validation_result');
            $element->appendChild($validationElement);
        } else {
            $validationElement = $this->createElement($document, 'validation_result');
            $element->appendChild($validationElement);
        }

        if (null !== $this->provenance) {
            $provenanceElement = $this->provenance->toXml($document, 'provenance');
            $element->appendChild($provenanceElement);
        } else {
            $provenanceElement = $this->createElement($document, 'provenance');
            $element->appendChild($provenanceElement);
        }

        return $element;
    }

    /**
     * Convert the dataset to XML string.
     *
     * @return string The XML representation
     */
    public function toXmlString(): string
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;

        $element = $this->toXml($document);
        $document->appendChild($element);

        return $document->saveXML();
    }
}
