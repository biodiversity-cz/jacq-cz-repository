<?php

declare(strict_types=1);

namespace App\UI\Front\OaiPmh;

use App\Services\AppConfiguration;
use App\Services\OaiPmh\MetadataFormat\CcmmFormat;
use App\Services\OaiPmh\MetadataFormat\DublinCoreFormat;
use App\Services\OaiPmh\MetadataFormat\MetadataFormatInterface;
use App\Services\OaiPmh\OaiPmhRecordProviderInterface;
use App\Services\RepositoryConfiguration;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 * OAI-PMH v2.0 presenter implementing all mandatory verbs.
 */
final class OaiPmhPresenter extends UnsecuredPresenter
{
    private const string OAI_PMH_VERSION = '2.0';
    private const string ADMIN_EMAIL = 'novotp@natur.cuni.cz'; // TODO: Make configurable
    private const string REPOSITORY_NAME = 'herbarium.biodiversity.cz';
    private const string REPOSITORY_DOMAIN = 'herbarium.biodiversity.cz'; // TODO: Make configurable

    private const int DEFAULT_PAGE_SIZE = 100;
    private const int MAX_PAGE_SIZE = 1000;

    /** @var MetadataFormatInterface[] */
    private array $metadataFormats;

    public function __construct(
        AppConfiguration $appConfiguration,
        private readonly OaiPmhRecordProviderInterface $recordProvider,
        private readonly DublinCoreFormat $dublinCoreFormat,
        private readonly CcmmFormat $ccmmFormat,
        private readonly RepositoryConfiguration $repositoryConfiguration,
    ) {
        parent::__construct($appConfiguration);

        $this->metadataFormats = [
            $this->dublinCoreFormat->getMetadataPrefix() => $this->dublinCoreFormat,
            $this->ccmmFormat->getMetadataPrefix() => $this->ccmmFormat,
        ];
    }

    public function actionDefault(?string $verb = null): void
    {
        $this->getHttpResponse()->setContentType('application/xml', 'utf-8');
        $this->getHttpResponse()->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
        $this->getHttpResponse()->setHeader('Pragma', 'no-cache');
        $this->getHttpResponse()->setHeader('Expires', '0');
        try {
            $writer = match ($verb) {
                'Identify' => $this->verbIdentify(),
                'ListMetadataFormats' => $this->verbListMetadataFormats(),
                'ListSets' => $this->verbListSets(),
                'ListIdentifiers' => $this->verbListIdentifiers(),
                'ListRecords' => $this->verbListRecords(),
                'GetRecord' => $this->verbGetRecord(),
                null => throw new BadRequestException('Missing verb parameter', 400),
                default => throw new BadRequestException('Illegal verb: '.$verb, 400),
            };
            $writer->endElement(); // OAI-PMH
            $writer->endDocument();
            echo $writer->outputMemory();
            $this->terminate();
        } catch (BadRequestException $e) {
            //            $this->error($e->getMessage());
            $writer = $this->createErrorResponse($e->getMessage(),
                $this->mapHttpCodeToOaiError($e->getCode()));
            $writer->endElement(); // OAI-PMH
            $writer->endDocument();
            echo $writer->outputMemory();
            $this->terminate();
        } catch (AbortException $e) {
            throw $e;
        } catch (\Throwable $e) {
            //             throw $e;
            $writer = $this->createErrorResponse('Internal repository error', 'badArgument');
            $writer->endElement(); // OAI-PMH
            $writer->endDocument();
            echo $writer->outputMemory();
            $this->terminate();
        }
    }

    private function verbIdentify(): \XMLWriter
    {
        $writer = $this->createXMLWriter('Identify');

        $writer->startElement('Identify');
        $writer->writeElement('repositoryName', self::REPOSITORY_NAME);
        $writer->writeElement('baseURL', $this->getBaseUrl());
        $writer->writeElement('protocolVersion', self::OAI_PMH_VERSION);
        $writer->writeElement('adminEmail', self::ADMIN_EMAIL);
        $writer->writeElement('earliestDatestamp',
            $this->recordProvider->getEarliestDatestamp()?->format('Y-m-d\TH:i:s\Z') ?? '1970-01-01T00:00:00Z');
        $writer->writeElement('deletedRecord', 'no');
        $writer->writeElement('granularity', 'YYYY-MM-DDThh:mm:ssZ');
        $writer->endElement();

        return $writer;
    }

    private function verbListMetadataFormats(): \XMLWriter
    {
        $writer = $this->createXMLWriter('ListMetadataFormats');

        $writer->startElement('ListMetadataFormats');
        foreach ($this->metadataFormats as $format) {
            $writer->startElement('metadataFormat');
            $writer->writeElement('metadataPrefix', $format->getMetadataPrefix());
            $writer->writeElement('schema', $format->getSchema());
            $writer->writeElement('metadataNamespace', $format->getMetadataNamespace());
            $writer->endElement();
        }
        $writer->endElement();

        return $writer;
    }

    private function verbListSets(): \XMLWriter
    {
        $writer = $this->createXMLWriter('ListMetadataFormats');

        $writer->startElement('ListSets');
        foreach ($this->recordProvider->getAvailableSets() as $setSpec => $setName) {
            $writer->startElement('set');
            $writer->writeElement('setSpec', $setSpec);
            $writer->writeElement('setName', $setName);
            $writer->endElement();
        }
        $writer->endElement();

        return $writer;
    }

    private function verbListIdentifiers(): \XMLWriter
    {
        return $this->getListRecordsOrIdentifiers(false);
    }

    private function verbListRecords(): \XMLWriter
    {
        return $this->getListRecordsOrIdentifiers(true);
    }

    private function getListRecordsOrIdentifiers(bool $includeMetadata): \XMLWriter
    {
        $request = $this->getHttpRequest();
        $metadataPrefix = $request->getQuery('metadataPrefix');
        $from = $request->getQuery('from');
        $until = $request->getQuery('until');
        $set = $request->getQuery('set');
        $resumptionToken = $request->getQuery('resumptionToken');

        // Validate metadata prefix
        if (!$resumptionToken && (!$metadataPrefix || !isset($this->metadataFormats[$metadataPrefix]))) {
            throw new BadRequestException('Invalid or missing metadataPrefix', 400);
        }

        // Parse resumption token or use parameters
        [$offset, $totalRecords, $metadataPrefix, $from, $until, $set] =
            $this->parseResumptionToken($resumptionToken, $metadataPrefix, $from, $until, $set);

        // Validate and parse dates
        $fromDate = $from ? $this->parseDate($from) : null;
        $untilDate = $until ? $this->parseDate($until) : null;

        if ($fromDate && $untilDate && $fromDate > $untilDate) {
            throw new BadRequestException('from date must be earlier than until date', 400);
        }

        // Get records
        $limit = self::DEFAULT_PAGE_SIZE;
        $records = $this->recordProvider->getRecords($fromDate, $untilDate, $set, $offset, $limit + 1);

        $verb = $includeMetadata ? 'ListRecords' : 'ListIdentifiers';
        $writer = $this->createXMLWriter($verb);

        $writer->startElement($verb);

        $format = $this->metadataFormats[$metadataPrefix];
        $recordsCount = 0;
        $hasMore = false;

        foreach ($records as $photo) {
            ++$recordsCount;

            if ($recordsCount > $limit) {
                $hasMore = true;
                break;
            }

            $this->writeRecordElement($writer, $photo, $format, $includeMetadata);
        }
        $writer->endElement();

        // Add resumption token if needed
        if ($hasMore) {
            $newOffset = $offset + $limit;
            $newToken = $this->createResumptionToken($newOffset, $totalRecords, $metadataPrefix, $from, $until, $set);

            $writer->startElement('resumptionToken');
            $writer->text($newToken);
            $writer->writeAttribute('cursor', (string) $offset);
            if ($totalRecords > 0) {
                $writer->writeAttribute('completeListSize', (string) $totalRecords);
            }
            $writer->endElement();
        } elseif ($resumptionToken) {
            // Empty resumption token to indicate end of list
            $writer->writeElement('resumptionToken');
        }

        return $writer;
    }

    private function verbGetRecord(): \XMLWriter
    {
        $request = $this->getHttpRequest();
        $identifier = $request->getQuery('identifier');
        $metadataPrefix = $request->getQuery('metadataPrefix');

        if (!$identifier) {
            throw new BadRequestException('Missing identifier parameter', 400);
        }

        if (!$metadataPrefix || !isset($this->metadataFormats[$metadataPrefix])) {
            throw new BadRequestException('Invalid or missing metadataPrefix', 400);
        }

        $photo = $this->recordProvider->getRecord($identifier);
        if (!$photo) {
            throw new BadRequestException('Record does not exist', 404);
        }

        $writer = $this->createXMLWriter('GetRecord');
        $writer->startElement('GetRecord');
        $format = $this->metadataFormats[$metadataPrefix];
        $this->writeRecordElement($writer, $photo, $format, true);
        $writer->endElement();

        return $writer;
    }

    private function writeRecordElement(
        \XMLWriter $writer,
        \App\Model\Database\Entity\Photos $photo,
        MetadataFormatInterface $format,
        bool $includeMetadata,
    ): void {
        $writer->startElement('record');

        // Header
        $writer->startElement('header');

        $identifier = $this->recordProvider->generateIdentifier($photo, self::REPOSITORY_DOMAIN);
        $writer->writeElement('identifier', $identifier);
        $writer->writeElement('datestamp', $photo->lastEdit->format('Y-m-d\TH:i:s\Z'));

        // Set specs
        $writer->writeElement('setSpec', $photo->herbarium->acronym);

        $writer->endElement(); // header

        // Metadata (jen pokud includeMetadata = true)
        if ($includeMetadata) {
            $writer->startElement('metadata');

            // $format->toXml() by měl vracet DOMDocument nebo DOMElement
            // pokud vrací DOMDocument, můžeme převést na string a vložit do writeru
            $metadataContent = $format->toXml($photo, $identifier);

            // Převod DOMDocument/DomElement na string
            $xmlString = $metadataContent instanceof \DOMDocument
                ? $metadataContent->saveXML($metadataContent->documentElement)
                : $metadataContent->ownerDocument->saveXML($metadataContent);

            $writer->writeRaw($xmlString);

            $writer->endElement(); // metadata
        }

        $writer->endElement(); // record
    }

    private function createXMLWriter(string $verb): \XMLWriter
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');

        // root element s namespace
        $writer->startElementNS(null, 'OAI-PMH', 'http://www.openarchives.org/OAI/2.0/');
        $writer->writeAttributeNS(
            'xsi',
            'schemaLocation',
            'http://www.w3.org/2001/XMLSchema-instance',
            'http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd'
        );

        // responseDate
        $writer->writeElement('responseDate', new \DateTimeImmutable()->format('Y-m-d\TH:i:s\Z'));

        // request element
        $writer->startElement('request');
        $writer->writeAttribute('verb', $verb);
        foreach ($this->getHttpRequest()->getQuery() as $param => $value) {
            if ('verb' !== $param && null !== $value && '' !== $value) {
                $writer->writeAttribute($param, (string) $value);
            }
        }
        $writer->text($this->getBaseUrl());
        $writer->endElement(); // request

        return $writer;
    }

    private function createErrorResponse(string $message, string $code): \XMLWriter
    {
        $writer = $this->createXMLWriter('');
        $writer->startElement('error');
        $writer->writeAttribute('code', $code);
        $writer->text($message);
        $writer->endElement();

        return $writer;
    }

    private function getBaseUrl(): string
    {
        $request = $this->getHttpRequest();

        return $request->getUrl()->getBaseUrl().'oai-pmh';
    }

    private function parseDate(string $date): \DateTimeInterface
    {
        // Support both date (YYYY-MM-DD) and datetime (YYYY-MM-DDTHH:MM:SSZ) formats
        $formats = ['Y-m-d\TH:i:s\Z', 'Y-m-d\TH:i:sP', 'Y-m-d'];

        foreach ($formats as $format) {
            $parsed = \DateTimeImmutable::createFromFormat($format, $date);
            if (false !== $parsed) {
                return $parsed;
            }
        }

        throw new BadRequestException('Invalid date format: '.$date, 400);
    }

    private function parseResumptionToken(
        ?string $token,
        ?string $metadataPrefix,
        ?string $from,
        ?string $until,
        ?string $set,
    ): array {
        if ($token) {
            $decoded = base64_decode($token, true);
            if (false === $decoded) {
                throw new BadRequestException('Invalid resumption token', 400);
            }

            $data = json_decode($decoded, true);
            if (!is_array($data)) {
                throw new BadRequestException('Invalid resumption token', 400);
            }

            return [
                $data['offset'] ?? 0,
                $data['total'] ?? 0,
                $data['metadataPrefix'] ?? 'oai_dc',
                $data['from'] ?? null,
                $data['until'] ?? null,
                $data['set'] ?? null,
            ];
        }

        return [0, $this->recordProvider->getTotalRecordsCount(), $metadataPrefix, $from, $until, $set];
    }

    private function createResumptionToken(
        int $offset,
        int $total,
        string $metadataPrefix,
        ?string $from,
        ?string $until,
        ?string $set,
    ): string {
        $data = [
            'offset' => $offset,
            'total' => $total,
            'metadataPrefix' => $metadataPrefix,
            'from' => $from,
            'until' => $until,
            'set' => $set,
        ];

        return base64_encode(json_encode($data));
    }

    private function mapHttpCodeToOaiError(int $code): string
    {
        return match ($code) {
            400 => 'badArgument',
            404 => 'idDoesNotExist',
            422 => 'cannotDisseminateFormat',
            503 => 'badResumptionToken',
            default => 'badArgument',
        };
    }
}
