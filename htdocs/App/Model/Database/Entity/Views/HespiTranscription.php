<?php

declare(strict_types=1);

namespace App\Model\Database\Entity\Views;

use App\Model\Database\Entity\Photos;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(readOnly: true)]
#[Table(name: 'hespi_transcription', schema: 'databots')]
class HespiTranscription
{
    #[Id]
    #[OneToOne(targetEntity: Photos::class, inversedBy: 'transcriptionHespi')]
    #[JoinColumn(name: 'photo_id', referencedColumnName: 'id', nullable: false)]
    public protected(set) Photos $photo;

    #[Column(name: 'computed_warning', type: 'string', nullable: true)]
    public protected(set) ?string $warning = null;
}
