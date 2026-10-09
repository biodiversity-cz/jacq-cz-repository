<?php

declare(strict_types=1);

namespace App\Services\OaiPmh\MetadataFormat;

use App\Model\CCMM\Enum\Language;
use App\Model\CCMM\Models\AccessRights;
use App\Model\CCMM\Models\Checksum;
use App\Model\CCMM\Models\ConformsToStandard;
use App\Model\CCMM\Models\Dataset;
use App\Model\CCMM\Models\DateType;
use App\Model\CCMM\Models\Description;
use App\Model\CCMM\Models\DescriptionText;
use App\Model\CCMM\Models\DescriptionType;
use App\Model\CCMM\Models\Distribution;
use App\Model\CCMM\Models\DistributionDownloadableFile;
use App\Model\CCMM\Models\DownloadUrl;
use App\Model\CCMM\Models\Format;
use App\Model\CCMM\Models\Identifier;
use App\Model\CCMM\Models\IdentifierScheme;
use App\Model\CCMM\Models\Keyword;
use App\Model\CCMM\Models\Licence;
use App\Model\CCMM\Models\Location;
use App\Model\CCMM\Models\MediaType;
use App\Model\CCMM\Models\MetadataIdentification;
use App\Model\CCMM\Models\OriginalRepository;
use App\Model\CCMM\Models\QualifiedAttribution;
use App\Model\CCMM\Models\RelatedResource;
use App\Model\CCMM\Models\Relation;
use App\Model\CCMM\Models\RelationType;
use App\Model\CCMM\Models\ResourceRelationType;
use App\Model\CCMM\Models\ResourceType;
use App\Model\CCMM\Models\Rights;
use App\Model\CCMM\Models\Role;
use App\Model\CCMM\Models\Subject;
use App\Model\CCMM\Models\SubjectScheme;
use App\Model\CCMM\Models\TimeInstant;
use App\Model\CCMM\Models\TimeReference;
use App\Model\Database\Entity\Photos;
use Nette\Application\LinkGenerator;

/**
 * CCMM metadata format.
 */
final class CcmmFormat implements MetadataFormatInterface
{
    public const string XML_SCHEMA = 'https://model.ccmm.cz/research-data/dataset/schema.xsd';
    public const string XML_NAMESPACE = 'https://schema.ccmm.cz/research-data/2.0';
    public const string NAME = 'Czech Core Metadata Model v2.0';

    public function __construct(private LinkGenerator $linkGenerator)
    {
    }

    public function getMetadataPrefix(): string
    {
        return 'ccmm-xml';
    }

    public function getSchema(): string
    {
        return self::XML_SCHEMA;
    }

    public function getMetadataNamespace(): string
    {
        return self::XML_NAMESPACE;
    }

    public function getFormatName(): string
    {
        return self::NAME;
    }

    // TODO pokud geometrii, tak jako WKT ve WGS84 aby to NMA mohl dobře zpracovávat
    public function toXml(mixed $item, string $oaiIdentifier): \DOMElement
    {
        if (!$item instanceof Photos) {
            throw new \InvalidArgumentException('Expected Photos entity.');
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');

        $dataset = new Dataset('https://n2t.net/' . $item->pid);

        foreach ($this->addDistributions($item) as $distribution) {
            $dataset->addDistribution($distribution);
        }

        $dataset->setResourceType($this->getResourceType());
        $dataset->setRawFundingReference($this->addFunding($item));
        $dataset->addIdentifier($this->getIdentifier($item));
        $dataset
            ->setKeyword(new Keyword($item->cetafHarvest?->title, Language::LA))
            ->setDescriptions($this->addDescriptions($item))
            ->setTitle('Image associated with a preserved herbarium specimen ' . $item->getFullSpecimenId())
            ->setTimeReferences($this->getDates($item))
            ->setPublicationYear($item->issuedAt?->format('Y'))
            ->setSubjects($this->getSubject())
            ->setQualifiedRelations($this->getQualifiedRelations($item))
            ->setRelatedResources($this->addRelatedResources($item))
            ->setMetadataIdentification($this->getMetadataIdentification($item))
            ->setAccessRights($this->getAccessRights());

        return $dataset->toXml($doc);
    }


    private function getMetadataIdentification(Photos $photo): MetadataIdentification
    {
        $qa = new QualifiedAttribution();
        $cs = new ConformsToStandard()->setIri(CcmmFormat::XML_NAMESPACE)->addLabel('Czech Core Metadata Model for Research Data 2.0.0', Language::EN);
        $or = new OriginalRepository()
            ->setIri($this->linkGenerator->link('Front:Home:'))
            ->addLabel('Původní repozitář', Language::CS)
            ->setDescription('')
            ->setQualifiedAttributions([$qa]);
        $item = new MetadataIdentification()->setQualifiedAttributions([$qa])
            ->setDateCreated($photo->issuedAt)
            ->setDateUpdated($photo->lastEdit)
            ->setConformsToStandard($cs)
            ->setOriginalRepository($or);
        return $item;
    }

    /**
     * @return Description[]
     */
    private function addDescriptions(Photos $photo): array
    {
        $descriptionType = new DescriptionType()
            ->setIri('https://w3id.org/tib/datacite/vocab/descriptionType/Abstract')
            ->addLabel('abstract', Language::EN);
        $description = new Description()
            ->setDescriptionType($descriptionType);
        if (null !== $photo->cetafHarvest) {
            $text = $photo->cetafHarvest?->title .
                ' ' . $photo->cetafHarvest?->locality .
                ' ' . $photo->cetafHarvest?->eventDate .
                ' [Botanical description of material sample harvested from ' . $photo->specimenPid .
                '; last updated on ' . $photo->cetafHarvest?->lastEdit->format('Y-m-d') . ']';
        } else {
            $text = 'Photo documenting herbarium specimen ' . $photo->specimenPid . '.';
        }
        $description->setDescriptionText(new DescriptionText($text, Language::EN));

        return [$description];
    }

    /**
     * @return RelatedResource[]
     */
    private function addRelatedResources(Photos $photo): array
    {
        $resourceType = new ResourceType()
            ->setIri('http://purl.org/coar/resource_type/S7R1-K5P0')
            ->addLabel('physical sample', Language::EN);
        $relationType = new ResourceRelationType()
            ->setIri('https://vocabs.ccmm.cz/registry/codelist/RelationType/Documents')
            ->addLabel('documents', Language::EN)
            ->addLabel('dokumentuje (co)', Language::CS);
        $specimen = new RelatedResource()
            ->setIri($photo->specimenPid)
            ->setTitle('Digital representation of the physical specimen')
            ->setResourceUrl($photo->specimenPid)
            ->setResourceType($resourceType)
            ->setResourceRelationType($relationType);

        return [$specimen];
    }

    private function buildIndividualDistribution(array $data): Distribution
    {

        $dataDownload = new DistributionDownloadableFile()
            ->setAccessUrl('https://biodiversity-cz.github.io/herbarium-documentation/')
            ->setDownloadUrl(new DownloadUrl()->setIri($data['iri']))
            ->setFormat($data['format'])
            ->setMediaType($data['mediaType'])
            ->addTitle('original data')
            ->setChecksum($data['checksum'] ?? null)
            ->setByteSize($data['byteSize'])
            ->setAccessRights($this->getRights())
            ->setLicense($this->getLicence());

        $distribution = new Distribution();
        $distribution->setDistributionDownloadableFile($dataDownload);
        return $distribution;
    }

    /**
     * @return Distribution[]
     */
    private function addDistributions(Photos $photo): array
    {
        $formatTif = new Format()
            ->addLabel('TIFF', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/TIFF');
        $mediaTypeTif = new MediaType()
            ->addLabel('TIFF', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/TIFF');
        $formatJP2 = new Format()
            ->addLabel('JPEG 2000', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/JPEG2000');
        $mediaTypeJP2 = new MediaType()
            ->addLabel('JPEG 2000', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/JPEG2000');
        $formatPng = new Format()
            ->addLabel('PNG', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/PNG');
        $mediaTypePng = new MediaType()
            ->addLabel('PNG', Language::EN)
            ->setIri('https://op.europa.eu/en/web/eu-vocabularies/concept/-/resource?uri=http://publications.europa.eu/resource/authority/file-type/PNG');

        $checksumMaster = new Checksum()
            ->setChecksumValue($photo->archiveFileChecksum)
            ->setAlgorithm('http://spdx.org/rdf/terms#checksumAlgorithm_md5');

        $data = [
            'master' => [
                'iri' => $this->linkGenerator->link('Front:Repository:ArchiveImage', [$photo->id]),
                'title' => 'original data',
                'description' => '',
                'documentation' => 'https://biodiversity-cz.github.io/herbarium-documentation/docs/services/download.html#service-master-file',
                'checksum' => $checksumMaster,
                'format' => $formatTif,
                'mediaType' => $mediaTypeTif,
                'byteSize' => $photo->archiveFileSize,
            ],
            'jpeg2000' => [
                'iri' => $this->linkGenerator->link('Front:Repository:Jp2Image', [$photo->id]),
                'title' => 'JPEG 2000',
                'description' => 'Serves full size image in JPEG 2000 format.',
                'documentation' => 'https://biodiversity-cz.github.io/herbarium-documentation/docs/services/download.html#service-jp2',
                'format' => $formatJP2,
                'mediaType' => $mediaTypeJP2,
                'byteSize' => $photo->JP2FileSize
            ],
            'thumb' => [
                'iri' => $this->linkGenerator->link('Front:Repository:DatabotThumbImage', [$photo->id]),
                'title' => '1280px thumbnail',
                'description' => 'Serves image as thumbnail suitable for AI processing with longer side equal to 1280px',
                'documentation' => 'https://biodiversity-cz.github.io/herbarium-documentation/docs/services/download.html#service-thumb',
                'format' => $formatPng,
                'mediaType' => $mediaTypePng,
                'byteSize' => (int)($photo->JP2FileSize / 100), //TODO fake - but in CCMM is mandatory
            ]
        ];

        foreach ($data as $item) {
            $items[] = $this->buildIndividualDistribution($item);
        }

        return $items;
    }

    private function getResourceType(): ResourceType
    {
        $element = new ResourceType();
        $element->setIri('http://purl.org/coar/resource_type/c_ecc8')
            ->addLabel('nepohyblivý obraz', Language::CS)
            ->addLabel('still image', Language::EN);

        return $element;
    }

    /**
     * @return Location[]
     */
    private function getLocation(Photos $photo): array
    {
        // TODO - cETAF harvest does not include coordinates - should we harvest elsewhere..?
        $location = new Location(new RelationType()
            ->setIri('̈́https://vocabs.repo.cz/LocationRelation/en/page/Refers')
            ->addLabel('Refers to the location', Language::EN)
            ->addLabel('Týká se lokace', Language::CS));

        return [$location];
    }

    private function getLicence(): Licence
    {
        $license = new Licence()
            ->setIri('https://creativecommons.org/licenses/by/4.0/');
        // ->addLabel('Attribution 4.0 International', Language::EN);

        return $license;
    }

    private function getAccessRights(): AccessRights
    {
        $accesRights = new AccessRights()
            ->setIri('http://purl.org/coar/access_right/c_abf2')
            ->addLabel('open access', Language::EN)
            ->addLabel('otevřený přístup', Language::CS);

        return $accesRights;
    }

    private function getRights(): Rights
    {
        $accesRights = new Rights()
            ->setIri('http://purl.org/coar/access_right/c_abf2')
            ->addLabel('open access', Language::EN)
            ->addLabel('otevřený přístup', Language::CS);

        return $accesRights;
    }

    /**
     * @return QualifiedAttribution[]
     */
    private function getQualifiedRelations(Photos $photo): array
    {
        $creatorRole = new Role()
            ->setIri('https://vocabs.ccmm.cz/registry/codelist/AgentRole/Creator')
            ->addLabel('Creator', Language::EN)
            ->addLabel('Autor', Language::CS);
        $creatorRelation = new Relation();
        $creator = new QualifiedAttribution()
            ->setRole($creatorRole)
            ->setRelation($creatorRelation);

        $publisherRole = new Role()
            ->setIri('https://vocabs.ccmm.cz/registry/codelist/AgentRole/Publisher')
            ->addLabel('Publisher', Language::EN)
            ->addLabel('Vydavatel', Language::CS);
        $publisherRelation = new Relation();
        $publisher = new QualifiedAttribution()
            ->setRole($publisherRole)
            ->setRelation($publisherRelation);

        return [$creator, $publisher];
    }

    /**
     * @return Subject[]
     */
    private function getSubject(): array
    {

        $element = new Subject(
            new SubjectScheme()
            ->setLabel('OECD FORD Subject Category (Frascati)', Language::EN)
            ->setIri('https://vocabs.ccmm.cz/registry/codelist/SubjectCategory/')
        )
            ->addLabel('Plant sciences, botany', Language::EN)
            ->setClassificationCode('10611')
            ->setIri('https://vocabs.ccmm.cz/registry/codelist/SubjectCategory/10000/10600/10611');

        return [$element];
    }

    /**
     * @return TimeReference[]
     */
    private function getDates(Photos $photo): array
    {
        $issued = new TimeReference();
        $time = new TimeInstant()->setDateTime($photo->lastEdit);
        $issued->setTimeInstant($time);
        $dateType = new DateType()
            ->setIri('https://vocabs.ccmm.cz/TimeReference/en/page/Issued')
            ->addLabel('Date Issued', Language::EN)
            ->addLabel('Datum vydání', Language::CS);
        $issued->setDateType($dateType);

        $updated = new TimeReference();
        $time = new TimeInstant()->setDateTime($photo->lastEdit);
        $updated->setTimeInstant($time);
        $dateType = new DateType()
            ->setIri('https://vocabs.ccmm.cz/TimeReference/en/page/Updated')
            ->addLabel('Date Updated', Language::EN)
            ->addLabel('Datum aktualizace', Language::CS);
        $updated->setDateType($dateType);

        return [$issued, $updated];
    }

    private function getIdentifier(Photos $photo): Identifier
    {
        $element = new Identifier();
        $scheme = new IdentifierScheme();
        $scheme->setIri('https://n2t.net/.info/ark')
            ->addLabel('ARK');
        $element->setIri('https://n2t.net/' . $photo->pid)
            ->setValue($photo->pid)
            ->setScheme($scheme);

        return $element;
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
