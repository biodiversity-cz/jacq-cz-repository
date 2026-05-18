<?php

declare(strict_types=1);

namespace App\Model\Database\Entity\Attributes;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait TCreatedAt
{
    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    public protected(set) \DateTimeImmutable $createdAt;

    public function setCreatedAt(): mixed
    {
        $this->createdAt = new \DateTimeImmutable();

        return $this;
    }
}
