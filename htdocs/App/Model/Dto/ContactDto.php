<?php

declare(strict_types=1);

namespace App\Model\Dto;

use App\Model\Database\Entity\Contact;

final class ContactDto
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $email,
        public ?string $position,
    ) {
    }

    public static function fromEntity(Contact $contact): self
    {
        return new self(
            id: $contact->id,
            name: $contact->getFullname(),
            email: $contact->email,
            position: $contact->description
        );
    }
}
