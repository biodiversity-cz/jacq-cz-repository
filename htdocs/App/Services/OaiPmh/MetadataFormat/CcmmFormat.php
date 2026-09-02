<?php

declare(strict_types=1);

namespace App\Services\OaiPmh\MetadataFormat;

use App\Model\CCMM\Enum\Language;
use App\Model\CCMM\Models\Checksum;
use App\Model\CCMM\Models\Dataset;
use App\Model\CCMM\Models\Distribution;
use App\Model\CCMM\Models\DistributionDataService;
use App\Model\CCMM\Models\DistributionDownloadableFile;
use App\Model\CCMM\Models\Documentation;
use App\Model\CCMM\Models\DownloadUrl;
use App\Model\CCMM\Models\Format;
use App\Model\CCMM\Models\MediaType;
use App\Model\Database\Entity\Photos;
use Nette\Application\LinkGenerator;

/**
 * CCMM metadata format.
 */
final class CcmmFormat implements MetadataFormatInterface
{
    public function __construct(private LinkGenerator $linkGenerator)
    {
    }

    public function getMetadataPrefix(): string
    {
        return 'ccmm-xml';
    }

    public function getSchema(): string
    {
        return 'https://model.ccmm.cz/research-data/dataset/schema.xsd';
    }

    public function getMetadataNamespace(): string
    {
        return 'https://schema.ccmm.cz/research-data/1.1';
    }

    public function getFormatName(): string
    {
        return 'Czech Core Metadata Model v1.1';
    }

    // TODO pokud geometrii, tak jako WKT ve WGS84 aby to NMA mohl dobře zpracovávat
    public function toXml(mixed $item, string $oaiIdentifier): \DOMElement
    {
        if (!$item instanceof Photos) {
            throw new \InvalidArgumentException('Expected Photos entity.');
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');

        $dataset = new Dataset();
        foreach ($this->addDistributions($item) as $distribution) {
            $dataset->addDistribution($distribution);
        }

        $dataset->setRawFundingReference($this->addFunding($item));

        return $dataset->toXml($doc);
    }

    /**
     * @return Distribution[]
     */
    private function addDistributions(Photos $photo): array
    {
        $items = [];

        // Databot thumbnails
        $dataService = new DistributionDataService();
        $documentation = new Documentation();
        $documentation
            ->setIri('https://biodiversity-cz.github.io/herbarium-documentation/docs/services/download.html#service-thumb');
        $dataService
            ->setIri($this->linkGenerator->link('Front:Repository:DatabotThumbImage', [$photo->id]))
            ->addTitle('1280px thumbnail', Language::EN)
            ->addDescription('Serves image as thumbnail suitable for AI processing with longer side equal to 1280px', Language::EN)
            ->setDocumentation($documentation);

        $distribution = new Distribution();
        $distribution->setDistributionDataService($dataService);
        $items[] = $distribution;

        // JPEG2000 fullsize
        $dataService = new DistributionDataService();
        $documentation = new Documentation();
        $documentation
            ->setIri('https://biodiversity-cz.github.io/herbarium-documentation/docs/services/download.html#service-jp2');
        $dataService
            ->setIri($this->linkGenerator->link('Front:Repository:Jp2Image', [$photo->id]))
            ->addTitle('JPEG 2000', Language::EN)
            ->addDescription('Serves full size image in JPEG 2000 format.', Language::EN)
            ->setDocumentation($documentation);

        $distribution = new Distribution();
        $distribution->setDistributionDataService($dataService);
        $items[] = $distribution;

        // TIFF Master
        $dataDownload = new DistributionDownloadableFile();
        $checksum = new Checksum()
            ->setChecksumValue($photo->archiveFileChecksum)
            ->setAlgorithm('md5');
        $format = new Format()
            ->addLabel('TIFF')
            ->addLabel('TIFF', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/TIFF');
        $mediaType = new MediaType()
            ->addLabel('TIFF')
            ->addLabel('TIFF', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/TIFF');
        $downloadUrl = new DownloadUrl()->setIri($this->linkGenerator->link('Front:Repository:ArchiveImage', [$photo->id]))->addLabel('original data', Language::EN);
        $documentation = new Documentation();
        $documentation
            ->setIri('https://biodiversity-cz.github.io/herbarium-documentation/docs/services/download.html#service-master-file');
        $dataDownload
            ->setDownloadUrl($downloadUrl)
            ->setFormat($format)
            ->setByteSize($photo->archiveFileSize)
            ->setChecksum($checksum)
            ->setMediaType($mediaType)
            ->addTitle('original data', Language::EN);

        $distribution = new Distribution();
        $distribution->setDistributionDownloadableFile($dataDownload);
        $items[] = $distribution;

        return $items;
    }

    private function addFunding(Photos $photo): ?string
    {
        $funding = $photo->funding;
        if (empty($funding)) {
            return null;
        }

        return $funding->ccmmFormat;
    }
}
