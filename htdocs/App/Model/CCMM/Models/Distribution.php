<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\XmlSerializable;
use App\Model\CCMM\Traits\XmlSerializableTrait;

/**
 * Represents a distribution which can be either a data service or downloadable file
 */
class Distribution implements XmlSerializable
{
    use XmlSerializableTrait;

    protected(set) ?DistributionDataService $distributionDataService = null;
    protected(set) ?DistributionDownloadableFile $distributionDownloadableFile = null;

    public function __construct() {
    }


    // Getters
    public function getDistributionDataService(): ?DistributionDataService {
        return $this->distributionDataService;
    }

    public function getDistributionDownloadableFile(): ?DistributionDownloadableFile {
        return $this->distributionDownloadableFile;
    }

    // Setters
    public function setDistributionDataService(?DistributionDataService $distributionDataService): self {
        $this->distributionDataService = $distributionDataService;
        return $this;
    }

    public function setDistributionDownloadableFile(?DistributionDownloadableFile $distributionDownloadableFile): self {
        $this->distributionDownloadableFile = $distributionDownloadableFile;
        return $this;
    }

/**
     * @inheritDoc
     */
    public function toXml(\DOMDocument $document, ?string $elementName = null): \DOMElement
    {
        $element = $this->createElement($document, $elementName ?? 'distribution');

        if ($this->getDistributionDataService() !== null) {
            $serviceElement = $this->getDistributionDataService()->toXml($document, 'distribution_data_service');
            $element->appendChild($serviceElement);
        }

        if ($this->getDistributionDownloadableFile() !== null) {
            $fileElement = $this->getDistributionDownloadableFile()->toXml($document, 'distribution_downloadable_file');
            $element->appendChild($fileElement);
        }

        return $element;
    }
}
