<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Enum\Language;
use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a distribution downloadable file.
 */
class DistributionDownloadableFile implements XmlSerializable
{
    use XmlSerializableTrait;

    public protected(set) ?string $iri = null;
    public protected(set) array $titles = [];
    public protected(set) ?DownloadUrl $downloadUrl = null;
    public protected(set) ?string $accessUrl = null;
    public protected(set) ?ConformsToSchema $conformsToSchema = null;
    public protected(set) ?Format $format = null;
    public protected(set) ?MediaType $mediaType = null;
    public protected(set) ?int $byteSize = null;
    public protected(set) ?Checksum $checksum = null;
    public protected(set) ?Licence $license = null;
    public protected(set) ?Rights $accessRights = null;

    public function setIri(?string $iri): self
    {
        $this->iri = $iri;

        return $this;
    }

    public function addTitle(string $title): self
    {
        $this->titles[] = $title;

        return $this;
    }

    public function setAccessUrl(?string $accessUrl): self
    {
        $this->accessUrl = $accessUrl;

        return $this;
    }

    public function setDownloadUrl(?DownloadUrl $downloadUrl): self
    {
        $this->downloadUrl = $downloadUrl;

        return $this;
    }

    public function setConformsToSchema(?ConformsToSchema $conformsToSchema): self
    {
        $this->conformsToSchema = $conformsToSchema;

        return $this;
    }

    public function setFormat(?Format $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function setMediaType(?MediaType $mediaType): self
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    public function setByteSize(?int $byteSize): self
    {
        $this->byteSize = $byteSize;

        return $this;
    }

    public function setChecksum(?Checksum $checksum): self
    {
        $this->checksum = $checksum;

        return $this;
    }

    public function setLicense(?Licence $license): DistributionDownloadableFile
    {
        $this->license = $license;
        return $this;
    }

    public function setAccessRights(?Rights $accessRights): DistributionDownloadableFile
    {
        $this->accessRights = $accessRights;
        return $this;
    }


    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'distribution_downloadable_file');

        if (null !== $this->iri) {
            $iriElement = $this->createElement($document, 'iri', $this->iri);
            $element->appendChild($iriElement);
        }

        if (!empty($this->titles)) {
            foreach ($this->titles as $text) {
                $titleElement = $this->createElement($document, 'title', $text);
                $element->appendChild($titleElement);
            }
        }

        $accessUrlElement = $this->createElement($document, 'access_url', $this->accessUrl);
        $element->appendChild($accessUrlElement);

        $this->appendChildIfNotNull($element, $this->downloadUrl, 'download_url');
        $this->appendChildIfNotNull($element, $this->conformsToSchema, 'conforms_to_schema');
        $this->appendChildIfNotNull($element, $this->format);


        $this->appendChildIfNotNull($element, $this->mediaType, 'media_type');

        if (null !== $this->byteSize) {
            $sizeElement = $this->createElement($document, 'byte_size', (string) $this->byteSize);
            $element->appendChild($sizeElement);
        }

        $this->appendChildIfNotNull($element, $this->checksum);
        $this->appendChildIfNotNull($element, $this->accessRights);
        $this->appendChildIfNotNull($element, $this->license);

        return $element;
    }
}
