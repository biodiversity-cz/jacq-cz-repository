<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Enum\Language;
use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a distribution downloadable file
 */
class DistributionDownloadableFile implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?string $iri = null;
    protected(set) array $titles = [];
    protected(set) ?AccessUrl $accessUrl = null;
    protected(set) ?DownloadUrl $downloadUrl = null;
    protected(set) ?ConformsToSchema $conformsToSchema = null;
    protected(set) ?Format $format = null;
    protected(set) ?MediaType $mediaType = null;
    protected(set) ?int $byteSize = null;
    protected(set) ?Checksum $checksum = null;

    public function __construct() {
    }


    // Getters
    public function getIri(): ?string {
        return $this->iri;
    }

    public function getTitles(): array {
        return $this->titles;
    }

    public function getAccessUrl(): ?AccessUrl {
        return $this->accessUrl;
    }

    public function getDownloadUrl(): ?DownloadUrl {
        return $this->downloadUrl;
    }

    public function getConformsToSchema(): ?ConformsToSchema {
        return $this->conformsToSchema;
    }

    public function getFormat(): ?Format {
        return $this->format;
    }

    public function getMediaType(): ?MediaType {
        return $this->mediaType;
    }

    public function getByteSize(): ?int {
        return $this->byteSize;
    }

    public function getChecksum(): ?Checksum {
        return $this->checksum;
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

    public function setAccessUrl(?AccessUrl $accessUrl): self {
        $this->accessUrl = $accessUrl;
        return $this;
    }

    public function setDownloadUrl(?DownloadUrl $downloadUrl): self {
        $this->downloadUrl = $downloadUrl;
        return $this;
    }

    public function setConformsToSchema(?ConformsToSchema $conformsToSchema): self {
        $this->conformsToSchema = $conformsToSchema;
        return $this;
    }

    public function setFormat(?Format $format): self {
        $this->format = $format;
        return $this;
    }

    public function setMediaType(?MediaType $mediaType): self {
        $this->mediaType = $mediaType;
        return $this;
    }

    public function setByteSize(?int $byteSize): self {
        $this->byteSize = $byteSize;
        return $this;
    }

    public function setChecksum(?Checksum $checksum): self {
        $this->checksum = $checksum;
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

        if (!empty($this->getTitles())) {
            foreach ($this->getTitles() as $lang => $text) {
                $titleElement = $this->createElement($document, 'title', $text);
                $titleElement->setAttribute('xml:lang', $lang);
                $element->appendChild($titleElement);
            }
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
