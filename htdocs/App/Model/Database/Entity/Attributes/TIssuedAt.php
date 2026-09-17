<?php

declare(strict_types=1);

namespace App\Model\Database\Entity\Attributes;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;

trait TIssuedAt
{
    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    public protected(set) \DateTimeImmutable $issuedAt;

    public function setIssuedAt(): mixed
    {
        $this->issuedAt = new \DateTimeImmutable();

        return $this;
    }
}
