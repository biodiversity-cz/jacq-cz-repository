<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Repository\MaintenanceRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: MaintenanceRepository::class)]
#[Table(name: 'maintenance', schema: 'front', options: ['comment' => 'Manually edited table with maintenance information.'])]
class Maintenance
{

    use TId;

    #[Column(nullable: false)]
    protected(set) string $message = '';

    #[Column(nullable: false, options: ['default' => 'info','comment' => 'success | info | warning | danger = Bootstrap contextual classes'])]
    protected(set) string $type = 'info';

    #[Column(type: 'datetime_immutable', nullable: true, options: ['comment' => 'The message will be hidden when expired'])]
    protected(set) ?\DateTimeImmutable $expiresAt = null;


    public function setMessage(string $message): Maintenance
    {
        $this->message = $message;
        return $this;
    }

    public function getType(string $prefix=''): string
    {
        return $prefix.$this->type;
    }

    public function setType(string $type): Maintenance
    {
        $this->type = $type;
        return $this;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): Maintenance
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }


}
