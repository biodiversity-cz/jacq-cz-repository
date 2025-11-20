<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\CetafSid;
use Doctrine\ORM\EntityManagerInterface;

class CetaSidService extends BaseEntityService
{

    protected string $entityName = CetafSid::class;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }


}
