<?php

declare(strict_types=1);

namespace App\Api\PubV1;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use App\Model\Database\Entity\Herbaria;
use App\Model\Dto\ContactDto;
use App\Model\Dto\HerbariaDto;
use Doctrine\ORM\EntityManagerInterface;
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
        return $this->cache->load(sprintf('%s:list', self::CACHE_NAMESPACE), function () {
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
        }, [Cache::Expire => '1 day']);
    }

    #[Apitte\OpenApi('summary: Get contact persons of the herbarium.')]
    #[Apitte\Path('/{id}/contacts')]
    #[Apitte\Method('GET')]
    #[Apitte\Response(description: 'Success', code: '200')]
    public function contacts(ApiRequest $request): array
    {
        $id = (int) $request->getParameter('id');

        return $this->cache->load(sprintf('%s:detail:%d', self::CACHE_NAMESPACE, $id), function () use ($id) {
            $herbarium = $this->entityManager
                ->getRepository(Herbaria::class)->findOneBy(['id' => $id]);
            if (null === $herbarium) {
                throw ClientErrorException::create()->withMessage('Herbarium not found')->withCode(IResponse::S404_NotFound);
            }

            return array_map(
                static fn ($contact) => ContactDto::fromEntity($contact),
                $herbarium->contacts->toArray()
            );
        }, [Cache::Expire => '1 day']);
    }
}
