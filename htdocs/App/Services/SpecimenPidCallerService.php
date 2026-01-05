<?php declare(strict_types=1);

namespace App\Services;

use App\Model\Database\Entity\ExternalDatabase;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

final class SpecimenPidCallerService
{
    /** @var array<string, callable> */
    private array $handlers = [];

    public function __construct(
        private readonly Client                 $client,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    /**
     * @param Photos[] $entities
     * @param int $concurrency
     */
    public function callAsync(array $entities, int $concurrency = 5): void
    {
        $requests = function ($entities) {
            foreach ($entities as $entity) {
                yield new Request('GET', $entity->getSpecimenPidApiEndpoint());
            }
        };

        $pool = new Pool($this->client, $requests($entities), [
            'concurrency' => $concurrency,

            'fulfilled' => function ($response, $index) use ($entities) {
                $entity = $entities[$index];
                $handler = $this->chooseHandler($entity);
                $handler($entity, $response);
                $this->em->persist($entity);
            },

            'rejected' => function ($reason, $index) use ($entities) {
                $entity = $entities[$index];
//                $response = ($reason instanceof RequestException && $reason->hasResponse())
//                    ? $reason->getResponse()
//                    : null;
                $entity->setLastEditAt();
                $this->em->persist($entity);
            },
        ]);

        $pool->promise()->wait();
        $this->em->flush();
    }

    private function chooseHandler(Photos $entity): callable
    {
        if ($entity->herbarium->externalDatabase->id === ExternalDatabase::JACQ){
            return [$this, 'jacqHandler'];
        }
        return  [$this, 'defaultHandler'];
    }

    private function defaultHandler(Photos $entity, $response): void
    {
        $status = $response?->getStatusCode();
        if (($status === 200 || $status === 303) && $response) {

            $entity->setSpecimenPid($entity->getSpecimenPidApiEndpoint());
            $entity->setStatus($this->em->getReference(PhotosStatus::class, PhotosStatus::SPECIMEN_CONTROL_OK));
            $entity->setLastEditAt();
        }
    }

    private function jacqHandler(Photos $entity, $response): void
    {
        $status = $response?->getStatusCode();
        if ($status === 200 && $response) {
            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            if (isset($data['stableIdentifierLatest']['stableIdentifier'])) {
                $entity->setSpecimenPid($data['stableIdentifierLatest']['stableIdentifier']);
                $entity->setStatus($this->em->getReference(PhotosStatus::class, PhotosStatus::SPECIMEN_CONTROL_OK));
            }
            $entity->setLastEditAt();
        }
    }
}
