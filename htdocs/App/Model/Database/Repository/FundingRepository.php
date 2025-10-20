<?php declare(strict_types = 1);

namespace App\Model\Database\Repository;

use App\Model\Database\Entity\Funding;
use Doctrine\DBAL\Result;
use Doctrine\ORM\QueryBuilder;
use Nette\Security\User;

/**
 * @method Funding|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Funding|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Funding[] findAll()
 * @method Funding[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Funding>
 */
class FundingRepository extends AbstractRepository
{

    public function findAllAvailable(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('p')->andWhere('(p.herbarium = :userHerbarium  OR p.herbarium IS NULL) OR :isAdmin = true')->setParameter('userHerbarium', $user->getIdentity()->getCurrentHerbariumId())->setParameter('isAdmin', $user->isInRole('ROLE_ADMIN'));
    }

    public function findAllAvailableActive(User $user): array
    {
        return $this->createQueryBuilder('p')->andWhere('((p.herbarium = :userHerbarium  OR p.herbarium IS NULL) AND p.active = true) OR :isAdmin = true')->setParameter('userHerbarium', $user->getIdentity()->getCurrentHerbariumId())->setParameter('isAdmin', $user->isInRole('ROLE_ADMIN'))->getQuery()->getResult();
    }

    public function findAllEditable(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('p')->andWhere('p.herbarium = :userHerbarium  OR :isAdmin = true')->setParameter('userHerbarium', $user->getIdentity()->getCurrentHerbariumId())->setParameter('isAdmin', $user->isInRole('ROLE_ADMIN'));
    }

    public function findEditable(int $id, User $user): QueryBuilder
    {
        return $this->createQueryBuilder('p')->andWhere('(p.herbarium = :userHerbarium  OR :isAdmin = true) AND p.id = :id')
            ->setParameter('userHerbarium', $user->getIdentity()->getCurrentHerbariumId())
            ->setParameter('isAdmin', $user->isInRole('ROLE_ADMIN'))
            ->setParameter('id', $id);
    }

    public function findAssignable(int $id, User $user): Funding
    {
        return $this->createQueryBuilder('p')->andWhere('((p.herbarium = :userHerbarium  OR p.herbarium IS NULL)  AND p.id = :id AND p.active = true ) OR :isAdmin = true')
            ->setParameter('userHerbarium', $user->getIdentity()->getCurrentHerbariumId())
            ->setParameter('isAdmin', $user->isInRole('ROLE_ADMIN'))
            ->setParameter('id', $id)->getQuery()->getSingleResult();
    }
}
