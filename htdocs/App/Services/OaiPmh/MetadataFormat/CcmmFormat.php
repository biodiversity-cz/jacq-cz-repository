<?php

declare(strict_types=1);

namespace App\Services\OaiPmh\MetadataFormat;

use App\Model\CCMM\Enum\Language;
use App\Model\CCMM\Models\AccessRights;
use App\Model\CCMM\Models\Address;
use App\Model\CCMM\Models\Checksum;
use App\Model\CCMM\Models\ContactPoint;
use App\Model\CCMM\Models\Dataset;
use App\Model\CCMM\Models\DateType;
use App\Model\CCMM\Models\Distribution;
use App\Model\CCMM\Models\DistributionDataService;
use App\Model\CCMM\Models\DistributionDownloadableFile;
use App\Model\CCMM\Models\Documentation;
use App\Model\CCMM\Models\DownloadUrl;
use App\Model\CCMM\Models\Format;
use App\Model\CCMM\Models\Identifier;
use App\Model\CCMM\Models\IdentifierScheme;
use App\Model\CCMM\Models\License;
use App\Model\CCMM\Models\MediaType;
use App\Model\CCMM\Models\QualifiedRelation;
use App\Model\CCMM\Models\RelatedResource;
use App\Model\CCMM\Models\Relation;
use App\Model\CCMM\Models\ResourceRelationType;
use App\Model\CCMM\Models\ResourceType;
use App\Model\CCMM\Models\Role;
use App\Model\CCMM\Models\Subject;
use App\Model\CCMM\Models\SubjectScheme;
use App\Model\CCMM\Models\TermsOfUse;
use App\Model\CCMM\Models\TimeInstant;
use App\Model\CCMM\Models\TimeReference;
use App\Model\CCMM\Models\Title;
use App\Model\Database\Entity\Photos;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
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

        $dataset->setResourceType($this->getResourceType());
        $dataset->setRawFundingReference($this->addFunding($item));
        $dataset->addIdentifier($this->getIdentifier($item));
        $dataset
            ->setTitle('Image associated with a preserved herbarium specimen '.$item->getFullSpecimenId())
            ->setTimeReferences($this->getDates($item))
            ->setPublicationYear($item->issuedAt?->format('Y'))
            ->setTermsOfUse($this->getLicence($item))
            ->setSubjects($this->getSubject())
            ->setQualifiedRelations($this->getQualifiedRelations($item))
            ->setRelatedResources($this->addRelatedResources($item));

        return $dataset->toXml($doc);
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
            ->setIri('documents')
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

    private function getResourceType(): ResourceType
    {
        $element = new ResourceType();
        $element->setIri('http://purl.org/coar/resource_type/c_ecc8')
            ->addLabel('nepohyblivý obraz', Language::CS)
            ->addLabel('still image', Language::EN);

        return $element;
    }

    private function getLicence(Photos $photo): TermsOfUse
    {
        $accesRights = new AccessRights()
            ->setIri('http://purl.org/coar/access_right/c_abf2')
            ->addLabel('open access', Language::EN)
            ->addLabel('otevřený přístup', Language::CS);
        $person = $photo->herbarium->contacts->matching(
            Criteria::create()->orderBy(['surname' => Order::Ascending])
        )
            ->first();
        $address = new Address()
            ->setFullAddress($photo->herbarium->address);
        $contactPoint = new ContactPoint()
            ->setEmail($person->email)
            ->setAddress($address);
        $license = new License()
            ->setIri('https://creativecommons.org/licenses/by/4.0/')
            ->addLabel('Attribution 4.0 International', Language::EN);
        $element = new TermsOfUse()
            ->setAccessRights($accesRights)
            ->setContactPoint($contactPoint)
            ->setLicense($license);

        return $element;
    }

    /**
     * @return QualifiedRelation[]
     */
    private function getQualifiedRelations(Photos $photo): array
    {
        $creatorRole = new Role()
            ->setIri('https://vocabs.ccmm.cz/registry/codelist/AgentRole/Creator')
            ->addLabel('Creator', Language::EN)
            ->addLabel('Autor', Language::CS);
        $creatorRelation = new Relation();
        $creator = new QualifiedRelation()
            ->setRole($creatorRole)
            ->setRelation($creatorRelation);

        $publisherRole = new Role()
            ->setIri('https://vocabs.ccmm.cz/registry/codelist/AgentRole/Publisher')
            ->addLabel('Publisher', Language::EN)
            ->addLabel('Vydavatel', Language::CS);
        $publisherRelation = new Relation();
        $publisher = new QualifiedRelation()
            ->setRole($publisherRole)
            ->setRelation($publisherRelation);

        return [$creator, $publisher];
    }

    /**
     * @return Subject[]
     */
    private function getSubject(): array
    {
        $title = new Title()
            ->setTitle('Plant sciences, botany')
            ->setLanguage(Language::EN);
        $subjectScheme = new SubjectScheme()
            ->setIri('https://vocabs.ccmm.cz/registry/codelist/SubjectCategory/');
        $element = new Subject()
            ->setIri('https://vocabs.ccmm.cz/registry/codelist/SubjectCategory/10000/10600/10611')
            ->setTitle($title)
            ->setSubjectScheme($subjectScheme);

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
        $element->setIri('https://n2t.net/'.$photo->pid)
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
