<?php declare(strict_types=1);

namespace App\Services\OaiPmh\MetadataFormat;

use App\Model\Database\Entity\Photos;
use App\Services\RepositoryConfiguration;

/**
 * Dublin Core (oai_dc) metadata format implementation
 */
final class DublinCoreFormat implements MetadataFormatInterface
{
    public function __construct(
        private readonly RepositoryConfiguration $repositoryConfig
    ) {
    }

    public function getMetadataPrefix(): string
    {
        return 'oai_dc';
    }

    public function getSchema(): string
    {
        return 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    }

    public function getMetadataNamespace(): string
    {
        return 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    }

    public function getFormatName(): string
    {
        return 'Simple Dublin Core';
    }

    public function toXml(mixed $item, string $oaiIdentifier): \DOMElement
    {
        if (!$item instanceof Photos) {
            throw new \InvalidArgumentException('Expected Photos entity.');
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');

        // Create root element with namespaces
        $dc = $doc->createElementNS($this->getMetadataNamespace(), 'oai_dc:dc');
        $dc->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $dc->setAttributeNS('http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation',
            $this->getMetadataNamespace() . ' ' . $this->getSchema());

        $doc->appendChild($dc);

        // dc:title - Specimen identifier as title
        $this->addElement($doc, $dc, 'dc:title',
            'Herbarium specimen ' . $item->getFullSpecimenId());

        // dc:creator - Herbarium name
        if ($item->getHerbarium()->getFullname()) {
            $this->addElement($doc, $dc, 'dc:creator', $item->getHerbarium()->getFullname());
        }

        // dc:subject - Type and general classification
        $this->addElement($doc, $dc, 'dc:subject', 'Herbarium specimen');
        $this->addElement($doc, $dc, 'dc:subject', 'Botanical collection');
        $this->addElement($doc, $dc, 'dc:subject', 'Digital image');

        // dc:description - Description with technical details
        $description = sprintf(
            'Digital image of herbarium specimen %s from %s. ',
            $item->getFullSpecimenId(),
            $item->getHerbarium()->getAcronym()
        );

        if ($item->getWidth() && $item->getHeight()) {
            $description .= sprintf('Image dimensions: %dx%d pixels. ',
                $item->getWidth(), $item->getHeight());
        }

        if ($item->getOriginalFilename()) {
            $description .= sprintf('Original filename: %s. ', $item->getOriginalFilename());
        }

        $this->addElement($doc, $dc, 'dc:description', trim($description));

        // dc:publisher - Herbarium as publisher
        $publisher = $item->getHerbarium()->getFullname() ?? $item->getHerbarium()->getAcronym();
        $this->addElement($doc, $dc, 'dc:publisher', $publisher);

        // dc:contributor - Same as publisher for now
        $this->addElement($doc, $dc, 'dc:contributor', $publisher);

        // dc:date - Creation and modification dates
        if ($item->getCreatedAt()) {
            $this->addElement($doc, $dc, 'dc:date', $item->getCreatedAt()->format('Y-m-d'));
        }

        if ($item->getLastEditAt() && $item->getLastEditAt() != $item->getCreatedAt()) {
            $this->addElement($doc, $dc, 'dc:date', $item->getLastEditAt()->format('Y-m-d'));
        }

        // dc:type - Resource type
        $this->addElement($doc, $dc, 'dc:type', 'Image');
        $this->addElement($doc, $dc, 'dc:type', 'StillImage');

        // dc:format - MIME types and file formats
        $this->addElement($doc, $dc, 'dc:format', 'image/tiff'); // Archive format
        if ($item->getJp2Filename()) {
            $this->addElement($doc, $dc, 'dc:format', 'image/jp2'); // JP2 format
        }

        // dc:identifier - Various identifiers
        $this->addElement($doc, $dc, 'dc:identifier', $oaiIdentifier);
        $this->addElement($doc, $dc, 'dc:identifier', $item->getExpectedJacqPid());
        $this->addElement($doc, $dc, 'dc:identifier', $item->getFullSpecimenId());

        // dc:source - Archive filename if available
        if ($item->getArchiveFilename()) {
            $this->addElement($doc, $dc, 'dc:source', $item->getArchiveFilename());
        }

        // dc:language - Default to Latin for botanical specimens
        $this->addElement($doc, $dc, 'dc:language', 'lat');

        // dc:relation - IIIF manifest and image server URLs
        if ($item->getJp2Filename()) {
            $iiifUrl = $this->repositoryConfig->getImageServerInfoUrl($item->getJp2Filename());
            $this->addElement($doc, $dc, 'dc:relation', $iiifUrl);

            $thumbnailUrl = $this->repositoryConfig->getImageServerUrlThumbnail($item->getJp2Filename());
            $this->addElement($doc, $dc, 'dc:relation', $thumbnailUrl);
        }

        // dc:coverage - Geographic coverage from herbarium
        if ($item->getHerbarium()->getAddress()) {
            $this->addElement($doc, $dc, 'dc:coverage', $item->getHerbarium()->getAddress());
        }

        // dc:rights - License information
        $license = $item->getHerbarium()->getLicense();
        if ($license) {
            // Assuming License entity has appropriate methods - adjust as needed
            $this->addElement($doc, $dc, 'dc:rights', 'Licensed content');
        }

        return $dc;
    }

    /**
     * Helper method to add DC elements
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
