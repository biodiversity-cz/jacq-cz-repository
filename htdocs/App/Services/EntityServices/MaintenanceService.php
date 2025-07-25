<?php declare(strict_types=1);

namespace App\Services\EntityServices;

use App\Model\Database\Entity\Maintenance;

class MaintenanceService extends BaseEntityService
{

    protected string $entityName = Maintenance::class;

    public function getValid(): array
    {
        return $this->repository->getValid();
    }


}
