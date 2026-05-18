<?php

declare(strict_types=1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\CetafSid;
use Doctrine\ORM\QueryBuilder;
use Nette\Security\User;

class CetafSidService extends BaseEntityService
{
    protected string $entityName = CetafSid::class;

    public function getDefaultDatasource(User $user): QueryBuilder
    {
        return $this->getRepository()
            ->createQueryBuilder('p')
            ->andWhere('p.herbarium = :userHerbarium  OR :isAdmin = true')
            ->setParameter('userHerbarium', $user->getIdentity()->getCurrentHerbariumId())
            ->setParameter('isAdmin', $user->isInRole('ROLE_ADMIN'));
    }
}
