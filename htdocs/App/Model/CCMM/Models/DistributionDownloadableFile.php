<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a distribution downloadable file
 */
class DistributionDownloadableFile implements XmlSerializable
{
    use XmlSerializableTrait;

    private ?string $iri = null;
    private ?string $title = null;
    private ?AccessUrl $accessUrl = null;
    private ?DownloadUrl $downloadUrl = null;
    private ?ConformsToSchema $conformsToSchema = null;
    private ?Format $format = null;
    private ?MediaType $mediaType = null;
    private ?int $byteSize = null;
    private ?Checksum $checksum = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->Iri;
    }

    public function getTitle(): ?string {
        return $this->Title;
    }

    public function getAccessUrl(): ?AccessUrl {
        return $this->AccessUrl;
    }

    public function getDownloadUrl(): ?DownloadUrl {
        return $this->DownloadUrl;
    }

    public function getConformsToSchema(): ?ConformsToSchema {
        return $this->ConformsToSchema;
    }

    public function getFormat(): ?Format {
        return $this->Format;
    }

    public function getMediaType(): ?MediaType {
        return $this->MediaType;
    }

    public function getByteSize(): ?int {
        return $this->ByteSize;
    }

    public function getChecksum(): ?Checksum {
        return $this->Checksum;
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

    public function setAccessUrl(?AccessUrl $accessUrl): self {
        $this->AccessUrl = $accessUrl;
        return $this;
    }

    public function setDownloadUrl(?DownloadUrl $downloadUrl): self {
        $this->DownloadUrl = $downloadUrl;
        return $this;
    }

    public function setConformsToSchema(?ConformsToSchema $conformsToSchema): self {
        $this->ConformsToSchema = $conformsToSchema;
        return $this;
    }

    public function setFormat(?Format $format): self {
        $this->Format = $format;
        return $this;
    }

    public function setMediaType(?MediaType $mediaType): self {
        $this->MediaType = $mediaType;
        return $this;
    }

    public function setByteSize(?int $byteSize): self {
        $this->ByteSize = $byteSize;
        return $this;
    }

    public function setChecksum(?Checksum $checksum): self {
        $this->Checksum = $checksum;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'distribution_downloadable_file');

        if ($this->getIri() !== null) {
            $iriElement = $this->createElement($document, 'iri', $this->getIri());
            $element->appendChild($iriElement);
        }

        if ($this->getTitle() !== null) {
            $titleElement = $this->createElement($document, 'title', $this->getTitle());
            $titleElement->setAttribute('xml:lang', 'cs');
            $element->appendChild($titleElement);
        }

        $this->appendChildIfNotNull($element, $this->getAccessUrl(), 'access_url');
        $this->appendChildIfNotNull($element, $this->getDownloadUrl(), 'download_url');
        $this->appendChildIfNotNull($element, $this->getConformsToSchema(), 'conforms_to_schema');
        $this->appendChildIfNotNull($element, $this->getFormat());
        $this->appendChildIfNotNull($element, $this->getMediaType(), 'media_type');

        if ($this->getByteSize() !== null) {
            $sizeElement = $this->createElement($document, 'byte_size', (string) $this->getByteSize());
            $element->appendChild($sizeElement);
        }

        $this->appendChildIfNotNull($element, $this->getChecksum());

        return $element;
    }
}
