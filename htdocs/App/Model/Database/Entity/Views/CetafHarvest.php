<?php

declare(strict_types=1);

namespace App\Model\Database\Entity\Views;

use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Entity\Photos;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(readOnly: true)]
#[Table(name: 'cetaf_harvest', schema: 'databots')]
class CetafHarvest
{
    use TLastEditAt;

    #[Id]
    #[OneToOne(targetEntity: Photos::class, inversedBy: 'cetafHarvest')]
    #[JoinColumn(name: 'photo_id', referencedColumnName: 'id', nullable: false)]
    public protected(set) Photos $photo;
    #[Column(name: 'title', type: 'string', nullable: true)]
    public protected(set) ?string $title = null;

    #[Column(name: 'created', type: 'string', nullable: true)]
    public protected(set) ?string $created = null;

    #[Column(name: 'creator', type: 'string', nullable: true)]
    public protected(set) ?string $creator = null;

    #[Column(name: 'publisher', type: 'string', nullable: true)]
    public protected(set) ?string $publisher = null;

    #[Column(name: 'thumbnail', type: 'string', nullable: true)]
    public protected(set) ?string $thumbnail = null;

    #[Column(name: 'genus', type: 'string', nullable: true)]
    public protected(set) ?string $genus = null;

    #[Column(name: 'family', type: 'string', nullable: true)]
    public protected(set) ?string $family = null;

    #[Column(name: 'description', type: 'string', nullable: true)]
    public protected(set) ?string $description = null;

    #[Column(name: 'country', type: 'string', nullable: true)]
    public protected(set) ?string $country = null;

    #[Column(name: 'locality', type: 'string', nullable: true)]
    public protected(set) ?string $locality = null;

    #[Column(name: 'event_date', type: 'string', nullable: true)]
    public protected(set) ?string $eventDate = null;

    #[Column(name: 'recorded_by', type: 'string', nullable: true)]
    public protected(set) ?string $recordedBy = null;

    #[Column(name: 'country_code', type: 'string', nullable: true)]
    public protected(set) ?string $countryCode = null;

    #[Column(name: 'field_number', type: 'string', nullable: true)]
    public protected(set) ?string $fieldNumber = null;

    #[Column(name: 'record_number', type: 'string', nullable: true)]
    public protected(set) ?string $recordNumber = null;

    #[Column(name: 'basis_of_record', type: 'string', nullable: true)]
    public protected(set) ?string $basisOfRecord = null;

    #[Column(name: 'catalog_number', type: 'string', nullable: true)]
    public protected(set) ?string $catalogNumber = null;

    #[Column(name: 'collection_code', type: 'string', nullable: true)]
    public protected(set) ?string $collectionCode = null;

    #[Column(name: 'scientific_name', type: 'string', nullable: true)]
    public protected(set) ?string $scientificName = null;

    #[Column(name: 'associated_media', type: 'string', nullable: true)]
    public protected(set) ?string $associatedMedia = null;

    #[Column(name: 'specific_epithet', type: 'string', nullable: true)]
    public protected(set) ?string $specificEpithet = null;

    #[Column(name: 'material_sample_id', type: 'string', nullable: true)]
    public protected(set) ?string $materialSampleId = null;

    #[Column(name: 'previous_identifications', type: 'string', nullable: true)]
    public protected(set) ?string $previousIdentifications = null;
}
