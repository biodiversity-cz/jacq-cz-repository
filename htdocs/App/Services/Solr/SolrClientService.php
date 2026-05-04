<?php declare(strict_types=1);

namespace App\Services\Solr;

use App\Model\Database\Entity\Databot;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Repository\DatabotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Solarium\Client;
use Solarium\Core\Query\DocumentInterface;


final readonly class SolrClientService
{
    public function __construct(
        protected(set) Client          $client,
        private EntityManagerInterface $entityManager,
    )
    {
    }

    public function flushPhotos(array $photos, bool $commit = false): void
    {
        if ($photos === []) {
            return;
        }

        $update = $this->client->createUpdate();

        foreach ($photos as $photo) {
            $doc = $update->createDocument();
            $doc = $this->prepareIngest($photo, $doc);
            if ($doc === null) {
                continue;
            }
            $update->addDocument($doc);
        }
        if ($commit) {
            $update->addCommit();
        }
        $this->client->update($update);

    }

    public function buildSuggest()
    {
        $this->client->createSuggester();
        $query = $this->client->createSuggester();
        $query->setHandler('suggest');
        $query->setBuild(true);

        $result = $this->client->suggester($query);
    }

    protected function debugSolrCall(mixed $update): void
    {
        $builder = $update->getRequestBuilder();
        $request = $builder->build($update);
        // základ z client configu
        $baseUri = $this->client->getEndpoint()->getBaseUri();
        // request path
        $path = $request->getUri();
        // finální URL
        $fullUrl = rtrim($baseUri, '/') . '/' . ltrim($path, '/');

        dump($fullUrl);
        dump($request->getMethod());   // POST
        dump($request->getUri());      // URL do Solr
        dump($request->getRawData());  // payload
    }

    protected function prepareIngest(Photos $photo, DocumentInterface $document): ?DocumentInterface
    {
        $cetafDatabot = $this->entityManager->getRepository(Databot::class)->getByName(DatabotRepository::CETAF);
        if ($cetafDatabot === null) {
            return null;
        }
        $cetafJson = $photo->getDatabotOkResultById($cetafDatabot)?->resultData ?? null;

        if ($cetafJson === null) {
            return null;
        }
        $document->setField('id', (string)$photo->pid);

        $date = $this->normalizeDwCEventDate($cetafJson['http://rs.tdwg.org/dwc/terms/eventDate'] ?? null);

        $document->setField('title', $cetafJson["http://purl.org/dc/terms/title"] ?? null);
        $document->setField('basis_of_record', 'PreservedSpecimen');
        $document->setField('herbarium_acronym', strtoupper($photo->herbarium->acronym));


        $document->setField('description', 'Photo of a herbarium specimen');
        $document->setField('locality', $cetafJson['http://rs.tdwg.org/dwc/terms/locality'] ?? null);

        // people
        $document->setField('creator', $cetafJson['http://purl.org/dc/terms/creator'] ?? null);
        $document->setField('recorded_by', $cetafJson['http://rs.tdwg.org/dwc/terms/recordedBy'] ?? null);

        // taxonomy
        $document->setField('scientific_name', $cetafJson['http://rs.tdwg.org/dwc/terms/scientificName'] ?? null);
        $document->setField('genus', $cetafJson['http://rs.tdwg.org/dwc/terms/genus'] ?? null);
        $document->setField('family', $cetafJson['http://rs.tdwg.org/dwc/terms/family'] ?? null);
        $document->setField('specific_epithet', $cetafJson['http://rs.tdwg.org/dwc/terms/specificEpithet'] ?? null);

        // geo
        $document->setField('country', $cetafJson['http://rs.tdwg.org/dwc/terms/country'] ?? null);
        $document->setField('country_code', $cetafJson['http://rs.tdwg.org/dwc/terms/countryCode'] ?? null);

        // dates
        $document->setField('event_date_from', $date['from'] ?? null);
        $document->setField('event_date_to', $date['to'] ?? null);
        $document->setField('event_date_raw', $cetafJson['http://rs.tdwg.org/dwc/terms/eventDate'] ?? null);
        $document->setField('created', $cetafJson['dc:created'] ?? null);

        // identifiers
        $document->setField('catalog_number', $cetafJson['http://rs.tdwg.org/dwc/terms/catalogNumber'] ?? null);
        $document->setField('collection_code', $cetafJson['http://rs.tdwg.org/dwc/terms/collectionCode'] ?? null);

        // misc
        $document->setField('material_sample_id', $cetafJson['http://rs.tdwg.org/dwc/terms/materialSampleID'] ?? null);

        $document->setField('previous_identifications', $cetafJson['http://rs.tdwg.org/dwc/terms/previousIdentifications'] ?? null);

        return $document;
    }

    function normalizeDwCEventDate(?string $input): array
    {

        if (empty($input)) {
            return [
                'from' => null,
                'to' => null,
            ];
        }
        $input = trim($input);
        // Interval A/B

        try {

            if (str_contains($input, '/')) {
                [$start, $end] = explode('/', $input, 2);

                // 2007-11-13/15 → doplnění dne
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) && preg_match('/^\d{2}$/', $end)) {
                    $end = substr($start, 0, 8) . $end;
                }

                $startRange = $this->normalizeSingleDateToRange($start);
                $endRange = $this->normalizeSingleDateToRange($end);

                return [
                    'from' => $startRange['from'],
                    'to' => $endRange['to'],
                ];
            }

            return $this->normalizeSingleDateToRange($input);
        } catch (\Exception $e) {
            return [
                'from' => null,
                'to' => null,
            ];
        }
    }

    function normalizeSingleDateToRange(string $value): array
    {
        $value = trim($value);
        $tz = new \DateTimeZone('UTC');

        // YYYY
        if (preg_match('/^\d{4}$/', $value)) {
            $from = new \DateTimeImmutable($value . '-01-01T00:00:00', $tz);
            $to = new \DateTimeImmutable($value . '-12-31T23:59:59.999999', $tz);

            return compact('from', 'to');
        }

        // YYYY-MM
        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            $from = new \DateTimeImmutable($value . '-01T00:00:00', $tz);

            $to = $from
                ->modify('first day of next month')
                ->modify('-1 microsecond');

            return compact('from', 'to');
        }

        // YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $from = new \DateTimeImmutable($value . 'T00:00:00', $tz);

            $to = $from
                ->modify('+1 day')
                ->modify('-1 microsecond');

            return compact('from', 'to');
        }

        // ISO datetime
        $dt = new \DateTimeImmutable($value);
        $dt = $dt->setTimezone($tz);

        // přesnost na minuty → interval 1 minuta
        $from = $dt;
        $to = $dt->modify('+1 minute')->modify('-1 microsecond');

        return compact('from', 'to');
    }

}
