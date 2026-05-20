<?php

declare(strict_types=1);

namespace App\Model\Dto;

use App\Model\Database\Entity\Herbaria;

final class HerbariaDto
{
    public function __construct(
        public int     $id,
        public string  $acronym,
        public ?string $logoUrl,
        public ?string $fullname,
        public ?string $address,
        public ?string $GRSCInstitution,
        public ?string $GRSCCollection
    )
    {
    }

    public static function fromEntity(Herbaria $herbarium): self
    {
        return new self(
            id: $herbarium->id,
            acronym: $herbarium->acronym,
            logoUrl: $herbarium->logo,
            fullname: $herbarium->fullname,
            address: $herbarium->address,
            GRSCInstitution: $herbarium->GRSciCollInstitutionID,
            GRSCCollection: $herbarium->GRSciCollCollectionID
        );
    }


}
