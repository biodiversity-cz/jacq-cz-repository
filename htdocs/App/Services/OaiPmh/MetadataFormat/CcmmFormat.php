<?php declare(strict_types=1);

namespace App\Services\OaiPmh\MetadataFormat;

use App\Model\CCMM\Enum\Language;
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
 * CCMM metadata format placeholder implementation
 * This is a basic structure - full implementation to be completed later
 */
final class CcmmFormat implements MetadataFormatInterface
{
    public function __construct(protected LinkGenerator $linkGenerator)
    {
    }

    public function getMetadataPrefix(): string
    {
        return 'ccmm';
    }

    public function getSchema(): string
    {
        return 'https://techlib.github.io/CCMM/dataset/schema.xsd';
    }

    public function getMetadataNamespace(): string
    {
        return 'https://github.com/techlib/CCMM';
    }

    public function getFormatName(): string
    {
        return 'Czech Core Metadata Model';
    }

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

        return $dataset->toXml($doc);

    }

    /**
     * @return Distribution[]
     */
    private function addDistributions(Photos $photo): array
    {
        $items = [];

        //Databot thumbnails
        $dataService = new DistributionDataService();
        $documentation = new Documentation();
        $documentation
            ->setIri('https://biodiversity-cz.github.io/herbarium-documentation/docs/services/databotThumb');
        $dataService
            ->setIri($this->linkGenerator->link('Front:Repository:DatabotThumbImage', [$photo->getId()]))
            ->addTitle('1280px thumbnail',Language::EN)
            ->addDescription('Serves image as thumbnail suitable for AI processing with longer side equal to 1280px',Language::EN)
            ->setDocumentation($documentation);

        $distribution = new Distribution();
        $distribution->setDistributionDataService($dataService);
        $items[] = $distribution;

        //JPEG2000 fullsize
        $dataService = new DistributionDataService();
        $documentation = new Documentation();
        $documentation
            ->setIri('https://biodiversity-cz.github.io/herbarium-documentation/docs/services/JPEG2000');
        $dataService
            ->setIri($this->linkGenerator->link('Front:Repository:JP2Image', [$photo->getId()]))
            ->addTitle('JPEG 2000',Language::EN)
            ->addDescription('Serves full size image in JPEG 2000 format.',Language::EN)
            ->setDocumentation($documentation);

        $distribution = new Distribution();
        $distribution->setDistributionDataService($dataService);
        $items[] = $distribution;

        //TIFF Master
        $dataDownload = new DistributionDownloadableFile();
        $format = new Format()
            ->addLabel('TIFF')
            ->addLabel('TIFF', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/TIFF');
        $mediaType = new MediaType()
            ->addLabel('TIFF')
            ->addLabel('TIFF', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/TIFF');
        $downloadUrl = new DownloadUrl()->setIri($this->linkGenerator->link('Front:Repository:ArchiveImage', [$photo->getId()]))->addLabel('original data', Language::EN);
        $documentation = new Documentation();
        $documentation
            ->setIri('https://biodiversity-cz.github.io/herbarium-documentation/docs/services/TIFF');
        $dataDownload
            ->setDownloadUrl($downloadUrl)
            ->setFormat($format)
            ->setMediaType($mediaType)
            ->addTitle('original data',Language::EN);

        $distribution = new Distribution();
        $distribution->setDistributionDownloadableFile($dataDownload);
        $items[] = $distribution;

        return $items;
    }

}
