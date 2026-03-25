<?php declare(strict_types=1);

namespace App\Services\Solr;

use App\Model\Database\Entity\Photos;
use Solarium\Client;
use Solarium\Core\Query\DocumentInterface;


final readonly class SolrClientService
{
    public function __construct(
        private Client $client,
    )
    {
    }

    public function indexPhoto(Photos $photo): void
    {
        $update = $this->client->createUpdate();
        $doc = $update->createDocument();
        $doc = $this->prepareIngest($photo, $doc);
        $update->addDocument($doc);
        $update->addCommit();

        $this->client->update($update);
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

    protected function prepareIngest(Photos $photo, DocumentInterface $document): DocumentInterface
    {
        $document->setField('id', (string)$photo->id);
        $document->setField('herbarium_acronym', strtoupper($photo->herbarium->acronym));

        return $document;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
