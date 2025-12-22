<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use App\Model\Database\Repository\AbstractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ObjectRepository;

abstract class BaseEntityService
{

    protected EntityRepository $repository;

    protected string $entityName;

    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
        $this->repository = $this->entityManager->getRepository($this->entityName);
    }

    /**
     * @return array|object[]
     */
    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param mixed[] $criteria
     * @param mixed[] $orderBy
     */
    public function findOneBy(array $criteria, array $orderBy = []): ?object
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    public function find(int $id): ?object
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset =null): array
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function getRepository()
    {
        return $this->repository;
    }

}
