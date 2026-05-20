<?php

declare(strict_types=1);

namespace App\Api\PubV1;

use Apitte\Core\Annotation\Controller as Apitte;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Caching\Cache;
use Nette\Caching\Storage;

#[Apitte\Path('/statistics')]
#[Apitte\Tag('Statistics')]
class StatisticsController extends BasePubV1Controller
{
    private Cache $cache;
    protected const string CACHE_NAMESPACE = 'statistics';

    public function __construct(protected EntityManagerInterface $entityManager, protected Storage $storage)
    {
        $this->cache = new Cache($storage, self::CACHE_NAMESPACE);
    }

    #[Apitte\OpenApi('summary: Get basic info about public data base of the repository.')]
    #[Apitte\Path('/general')]
    #[Apitte\Method('GET')]
    #[Apitte\Response(description: 'Success', code: '200')]
    public function version(): array
    {
        return $this->cache->load(self::CACHE_NAMESPACE.'/general', function () {
            return [
                'institutions' => $this->entityManager
                    ->getRepository(Herbaria::class)
                    ->count(),
                'photos' => $this->entityManager
                    ->getRepository(Photos::class)
                    ->countOfPublic(),
            ];
        });
    }
}
