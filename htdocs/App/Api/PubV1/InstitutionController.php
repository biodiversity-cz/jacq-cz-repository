<?php

declare(strict_types=1);

namespace App\Api\PubV1;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Dto\ContactDto;
use App\Model\Dto\HerbariaDto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Http\IResponse;

#[Apitte\Path('/institutions')]
#[Apitte\Tag('Institutions')]
class InstitutionController extends BasePubV1Controller
{
    private Cache $cache;
    protected const string CACHE_NAMESPACE = 'institutions';

    public function __construct(protected EntityManagerInterface $entityManager, protected Storage $storage)
    {
        $this->cache = new Cache($storage, self::CACHE_NAMESPACE);
    }

    #[Apitte\OpenApi('summary: Get list of herbaria involved in the repository.')]
    #[Apitte\Path('/')]
    #[Apitte\Method('GET')]
    #[Apitte\Response(description: 'Success', code: '200')]
    public function version(): array
    {
        return $this->cache->load(self::CACHE_NAMESPACE.'/general', function () {
            $entities = $this->entityManager
                ->getRepository(Herbaria::class)
                ->findBy(
                    criteria: [],
                    orderBy: ['acronym' => 'ASC']
                );

            return array_map(
                static fn (Herbaria $herbarium) => HerbariaDto::fromEntity($herbarium),
                $entities
            );
        });
    }

    #[Apitte\OpenApi('summary: Get contact persons of the herbarium.')]
    #[Apitte\Path('/{id}/contacts')]
    #[Apitte\Method('GET')]
    #[Apitte\Response(description: 'Success', code: '200')]

    public function contacts(ApiRequest $request): array
    {
        try {
            $herbarium = $this->entityManager
                ->getRepository(Herbaria::class)->findOneBy(['id' =>$request->getParameter('id')]);
        } catch (EntityNotFoundException $e) {
            throw ClientErrorException::create()
                ->withMessage('Herbarium not found')
                ->withCode(IResponse::S404_NotFound);
        }

        return array_map(
            static fn ($contact) => ContactDto::fromEntity($contact),
            $herbarium->contacts->toArray()
        );
    }
}
