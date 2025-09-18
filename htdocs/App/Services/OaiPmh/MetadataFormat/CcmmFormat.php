<?php declare(strict_types=1);

namespace App\Services\OaiPmh\MetadataFormat;

use App\Model\Database\Entity\Photos;

/**
 * CCMM metadata format placeholder implementation
 * This is a basic structure - full implementation to be completed later
 */
final class CcmmFormat implements MetadataFormatInterface
{
    public function getMetadataPrefix(): string
    {
        return 'ccmm';
    }

    public function getSchema(): string
    {
        // TODO: Replace with actual CCMM schema URL when available
        return 'http://example.com/ccmm.xsd';
    }

    public function getMetadataNamespace(): string
    {
        // TODO: Replace with actual CCMM namespace when available
        return 'http://example.com/ccmm/';
    }

    public function getFormatName(): string
    {
        return 'CCMM Format';
    }

    public function toXml(mixed $item, string $oaiIdentifier): \DOMElement
    {
        if (!$item instanceof Photos) {
            throw new \InvalidArgumentException('Expected Photos entity.');
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');

        // Create root element with placeholder namespace
        $ccmm = $doc->createElementNS($this->getMetadataNamespace(), 'ccmm:record');
        $ccmm->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation',
            $this->getMetadataNamespace() . ' ' . $this->getSchema());

        $doc->appendChild($ccmm);

        // TODO: Implement actual CCMM field mapping
        // Placeholder basic structure:

        // Basic identification
        $this->addElement($doc, $ccmm, 'ccmm:identifier', $oaiIdentifier);
        $this->addElement($doc, $ccmm, 'ccmm:specimenId', $item->getFullSpecimenId());

        // Institution information
        $institution = $doc->createElement('ccmm:institution');
        $this->addElement($doc, $institution, 'ccmm:code', $item->getHerbarium()->getAcronym());
        if ($item->getHerbarium()->getFullname()) {
            $this->addElement($doc, $institution, 'ccmm:name', $item->getHerbarium()->getFullname());
        }
        $ccmm->appendChild($institution);

        // Digital object information
        $digitalObject = $doc->createElement('ccmm:digitalObject');
        $this->addElement($doc, $digitalObject, 'ccmm:type', 'image');
        if ($item->getWidth() && $item->getHeight()) {
            $this->addElement($doc, $digitalObject, 'ccmm:width', (string) $item->getWidth());
            $this->addElement($doc, $digitalObject, 'ccmm:height', (string) $item->getHeight());
        }
        if ($item->getOriginalFilename()) {
            $this->addElement($doc, $digitalObject, 'ccmm:originalFilename', $item->getOriginalFilename());
        }
        $ccmm->appendChild($digitalObject);

        // Temporal information
        if ($item->getCreatedAt()) {
            $this->addElement($doc, $ccmm, 'ccmm:dateCreated', $item->getCreatedAt()->format('c'));
        }
        if ($item->getLastEditAt()) {
            $this->addElement($doc, $ccmm, 'ccmm:dateModified', $item->getLastEditAt()->format('c'));
        }

        // TODO: Add more CCMM-specific fields as needed:
        // - Taxonomic information
        // - Collection event data
        // - Geographic coordinates
        // - Collector information
        // - Determination history
        // - etc.

        return $ccmm;
    }

    /**
     * Helper method to add CCMM elements
     */
    private function addElement(\DOMDocument $doc, \DOMElement $parent, string $name, string $value): void
    {
        if (!empty(trim($value))) {
            $element = $doc->createElement($name);
            $element->appendChild($doc->createTextNode(trim($value)));
            $parent->appendChild($element);
        }
    }
}
