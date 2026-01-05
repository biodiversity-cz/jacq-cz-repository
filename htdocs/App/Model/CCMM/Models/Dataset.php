<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a dataset with all its metadata components
 */
class Dataset implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?string $iri = null;
    protected(set) ?MetadataIdentification $metadataIdentification = null;
    protected(set) array $identifiers = [];
    protected(set) ?string $version = null;
    protected(set) ?string $title = null;
    protected(set) array $alternateTitles = [];
    protected(set) array $qualifiedRelations = [];
    protected(set) ?int $publicationYear = null;
    protected(set) array $timeReferences = [];
    protected(set) ?ResourceType $resourceType = null;
    protected(set) ?string $primaryLanguage = null;
    protected(set) array $otherLanguages = [];
    protected(set) ?TermsOfUse $termsOfUse = null;
    protected(set) array $subjects = [];
    protected(set) array $descriptions = [];
    protected(set) array $locations = [];
    protected(set) array $fundingReferences = [];
    /**
     * the Funding structure is overcomplicated for herbaria purposes, allow storing XL fragment to create OAI-PMH in case of Funding
     */
    protected(set) ?string $fundingReferencesRaw = null;
    protected(set) array $relatedResources = [];
    protected(set) array $distributions = [];
    protected(set) ?ValidationResult $validationResult = null;
    protected(set) ?Provenance $provenance = null;

    public function __construct() {
    }

    // Getters
    public function getIri(): ?string {
        return $this->iri;
    }

    public function getMetadataIdentification(): ?MetadataIdentification {
        return $this->metadataIdentification;
    }

    public function getIdentifiers(): array {
        return $this->identifiers;
    }

    public function getVersion(): ?string {
        return $this->version;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function getAlternateTitles(): array {
        return $this->alternateTitles;
    }

    public function getQualifiedRelations(): array {
        return $this->qualifiedRelations;
    }

    public function getPublicationYear(): ?int {
        return $this->publicationYear;
    }

    public function getTimeReferences(): array {
        return $this->timeReferences;
    }

    public function getResourceType(): ?ResourceType {
        return $this->resourceType;
    }

    public function getPrimaryLanguage(): ?string {
        return $this->primaryLanguage;
    }

    public function getOtherLanguages(): array {
        return $this->otherLanguages;
    }

    public function getTermsOfUse(): ?TermsOfUse {
        return $this->termsOfUse;
    }

    public function getSubjects(): array {
        return $this->subjects;
    }

    public function getDescriptions(): array {
        return $this->descriptions;
    }

    public function getLocations(): array {
        return $this->locations;
    }

    public function getFundingReferences(): array {
        return $this->fundingReferences;
    }

    public function getRelatedResources(): array {
        return $this->relatedResources;
    }

    public function getDistributions(): array {
        return $this->distributions;
    }

    public function getValidationResult(): ?ValidationResult {
        return $this->validationResult;
    }

    public function getProvenance(): ?Provenance {
        return $this->provenance;
    }

    // Setters
    public function setIri(?string $iri): self {
        $this->iri = $iri;
        return $this;
    }

    public function setMetadataIdentification(?MetadataIdentification $metadataIdentification): self {
        $this->metadataIdentification = $metadataIdentification;
        return $this;
    }

    public function setIdentifiers(array $identifiers): self {
        $this->identifiers = $identifiers;
        return $this;
    }

    public function addIdentifier(Identifier $identifier): self {
        $this->identifiers[] = $identifier;
        return $this;
    }

    public function setVersion(?string $version): self {
        $this->version = $version;
        return $this;
    }

    public function setTitle(?string $title): self {
        $this->title = $title;
        return $this;
    }

    public function setAlternateTitles(array $alternateTitles): self {
        $this->alternateTitles = $alternateTitles;
        return $this;
    }

    public function addAlternateTitle(AlternateTitle $alternateTitle): self {
        $this->alternateTitles[] = $alternateTitle;
        return $this;
    }

    public function setQualifiedRelations(array $qualifiedRelations): self {
        $this->qualifiedRelations = $qualifiedRelations;
        return $this;
    }

    public function addQualifiedRelation(QualifiedRelation $qualifiedRelation): self {
        $this->qualifiedRelations[] = $qualifiedRelation;
        return $this;
    }

    public function setPublicationYear(?int $publicationYear): self {
        $this->publicationYear = $publicationYear;
        return $this;
    }

    public function setTimeReferences(array $timeReferences): self {
        $this->timeReferences = $timeReferences;
        return $this;
    }

    public function addTimeReference(TimeReference $timeReference): self {
        $this->timeReferences[] = $timeReference;
        return $this;
    }

    public function setResourceType(?ResourceType $resourceType): self {
        $this->resourceType = $resourceType;
        return $this;
    }

    public function setPrimaryLanguage(?string $primaryLanguage): self {
        $this->primaryLanguage = $primaryLanguage;
        return $this;
    }

    public function setOtherLanguages(array $otherLanguages): self {
        $this->otherLanguages = $otherLanguages;
        return $this;
    }

    public function addOtherLanguage(string $otherLanguage): self {
        $this->otherLanguages[] = $otherLanguage;
        return $this;
    }

    public function setTermsOfUse(?TermsOfUse $termsOfUse): self {
        $this->termsOfUse = $termsOfUse;
        return $this;
    }

    public function setSubjects(array $subjects): self {
        $this->subjects = $subjects;
        return $this;
    }

    public function addSubject(Subject $subject): self {
        $this->subjects[] = $subject;
        return $this;
    }

    public function setDescriptions(array $descriptions): self {
        $this->descriptions = $descriptions;
        return $this;
    }

    public function addDescription(Description $description): self {
        $this->descriptions[] = $description;
        return $this;
    }

    public function setLocations(array $locations): self {
        $this->locations = $locations;
        return $this;
    }

    public function addLocation(Location $location): self {
        $this->locations[] = $location;
        return $this;
    }

    public function setFundingReferences(array $fundingReferences): self {
        $this->fundingReferences = $fundingReferences;
        return $this;
    }

    public function addFundingReference(FundingReference $fundingReference): self {
        $this->fundingReferences[] = $fundingReference;
        return $this;
    }

    public function setRawFundingReference(?string $fundingReference): self {
        $this->fundingReferencesRaw = $fundingReference;
        return $this;
    }

    public function getRawFundingReferences(): ?string
    {
        return $this->fundingReferencesRaw;

    }

    public function setRelatedResources(array $relatedResources): self {
        $this->relatedResources = $relatedResources;
        return $this;
    }

    public function addRelatedResource(RelatedResource $relatedResource): self {
        $this->relatedResources[] = $relatedResource;
        return $this;
    }

    public function setDistributions(array $distributions): self {
        $this->distributions = $distributions;
        return $this;
    }

    public function addDistribution(Distribution $distribution): self {
        $this->distributions[] = $distribution;
        return $this;
    }

    public function setValidationResult(?ValidationResult $validationResult): self {
        $this->validationResult = $validationResult;
        return $this;
    }

    public function setProvenance(?Provenance $provenance): self {
        $this->provenance = $provenance;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        // Create the root element with namespaces
        $element = $document->createElementNS('https://schema.ccmm.cz/research-data/1.0', 'ccmm:dataset');
        $element->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation', 'https://schema.ccmm.cz/research-data/1.0 https://raw.githubusercontent.com/techlib/CCMM/refs/heads/main/dataset/schema.xsd');
        $element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gml', 'http://www.opengis.net/gml/3.2');
        $element->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        if ($this->getIri() !== null) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        $this->appendChildIfNotNull($element, $this->getMetadataIdentification(), 'metadata_identification');

        foreach ($this->getIdentifiers() as $identifier) {
            $identifierElement = $identifier->toXml($document);
            $element->appendChild($identifierElement);
        }

        if ($this->version !== null) {
            $versionElement = $this->createElement($document, 'version', $this->version);
            $element->appendChild($versionElement);
        }

        if ($this->getTitle() !== null) {
            $titleElement = $this->createElement($document, 'title', $this->getTitle());
            $element->appendChild($titleElement);
        }

        foreach ($this->getAlternateTitles() as $alternateTitle) {
            $altTitleElement = $alternateTitle->toXml($document);
            $element->appendChild($altTitleElement);
        }

        foreach ($this->getQualifiedRelations() as $qualifiedRelation) {
            $qualifiedRelationElement = $qualifiedRelation->toXml($document);
            $element->appendChild($qualifiedRelationElement);
        }

        if ($this->getPublicationYear() !== null) {
            $yearElement = $this->createElement($document, 'publication_year', (string) $this->getPublicationYear());
            $element->appendChild($yearElement);
        }

        foreach ($this->getTimeReferences() as $timeReference) {
            $timeRefElement = $timeReference->toXml($document);
            $element->appendChild($timeRefElement);
        }

        $this->appendChildIfNotNull($element, $this->getResourceType(), 'resource_type');

        if ($this->getPrimaryLanguage() !== null) {
            $primaryLangElement = $this->createElement($document, 'primary_language');
            $iriElement = $this->createElement($document, 'iri', $this->getPrimaryLanguage());
            $primaryLangElement->appendChild($iriElement);
            $element->appendChild($primaryLangElement);
        }

        foreach ($this->getOtherLanguages() as $otherLanguage) {
            $otherLangElement = $this->createElement($document, 'other_language');
            $iriElement = $this->createElement($document, 'iri', $otherLanguage);
            $otherLangElement->appendChild($iriElement);
            $element->appendChild($otherLangElement);
        }

        $this->appendChildIfNotNull($element, $this->getTermsOfUse(), 'terms_of_use');

        foreach ($this->getSubjects() as $subject) {
            $subjectElement = $subject->toXml($document);
            $element->appendChild($subjectElement);
        }

        foreach ($this->getDescriptions() as $description) {
            $descriptionElement = $description->toXml($document);
            $element->appendChild($descriptionElement);
        }

        foreach ($this->getLocations() as $location) {
            $locationElement = $location->toXml($document);
            $element->appendChild($locationElement);
        }

        foreach ($this->getFundingReferences() as $fundingReference) {
            $fundingElement = $fundingReference->toXml($document);
            $element->appendChild($fundingElement);
        }

        if(!empty($this->fundingReferencesRaw)){
            $fragment = $document->createDocumentFragment();
            $fragment->appendXML($this->getRawFundingReferences());
            $element->appendChild($fragment);
        }

        foreach ($this->getRelatedResources() as $relatedResource) {
            $relatedElement = $relatedResource->toXml($document);
            $element->appendChild($relatedElement);
        }

        foreach ($this->getDistributions() as $distribution) {
            $distributionElement = $distribution->toXml($document);
            $element->appendChild($distributionElement);
        }

        // Add empty validation_result and provenance elements as placeholders
        if ($this->getValidationResult() !== null) {
            $validationElement = $this->getValidationResult()->toXml($document, 'validation_result');
            $element->appendChild($validationElement);
        } else {
            $validationElement = $this->createElement($document, 'validation_result');
            $element->appendChild($validationElement);
        }

        if ($this->getProvenance() !== null) {
            $provenanceElement = $this->getProvenance()->toXml($document, 'provenance');
            $element->appendChild($provenanceElement);
        } else {
            $provenanceElement = $this->createElement($document, 'provenance');
            $element->appendChild($provenanceElement);
        }

        return $element;
    }

    /**
     * Convert the dataset to XML string
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
