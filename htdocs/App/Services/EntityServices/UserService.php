<?php declare(strict_types = 1);

namespace App\Services\EntityServices;

 use App\Model\Database\Entity\User;

class UserService extends BaseEntityService
{

    protected string $entityName = User::class;

}
