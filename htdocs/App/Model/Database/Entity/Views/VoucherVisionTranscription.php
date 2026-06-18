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
#[Table(name: 'vv_transcription', schema: 'databots')]
class VoucherVisionTranscription
{
    #[Id]
    #[OneToOne(targetEntity: Photos::class, inversedBy: 'transcription')]
    #[JoinColumn(name: 'photo_id', referencedColumnName: 'id', nullable: false)]
    public protected(set) Photos $photo;

    #[Column(name: 'catalog_number', type: 'string', nullable: true)]
    public protected(set) ?string $catalogNumber = null;

    #[Column(name: 'country', type: 'string', nullable: true)]
    public protected(set) ?string $country = null;

    #[Column(name: 'county', type: 'string', nullable: true)]
    public protected(set) ?string $county = null;

    #[Column(name: 'locality', type: 'string', nullable: true)]
    public protected(set) ?string $locality = null;

    #[Column(name: 'continent', type: 'string', nullable: true)]
    public protected(set) ?string $continent = null;

    #[Column(name: 'identified_by', type: 'string', nullable: true)]
    public protected(set) ?string $identifiedBy = null;

    #[Column(name: 'date_identified', type: 'string', nullable: true)]
    public protected(set) ?string $dateIdentified = null;

    #[Column(name: 'recorded_by', type: 'string', nullable: true)]
    public protected(set) ?string $recordedBy = null;

    #[Column(name: 'state_province', type: 'string', nullable: true)]
    public protected(set) ?string $stateProvince = null;

    #[Column(name: 'event_date', type: 'string', nullable: true)]
    public protected(set) ?string $eventDate = null;

    #[Column(name: 'verbatim_event_date', type: 'string', nullable: true)]
    public protected(set) ?string $verbatimEventDate = null;

    #[Column(name: 'occurrence_remarks', type: 'text', nullable: true)]
    public protected(set) ?string $occurrenceRemarks = null;

    #[Column(name: 'decimal_latitude', type: 'string', nullable: true)]
    public protected(set) ?string $decimalLatitude = null;

    #[Column(name: 'decimal_longitude', type: 'string', nullable: true)]
    public protected(set) ?string $decimalLongitude = null;

    #[Column(name: 'verbatim_coordinates', type: 'string', nullable: true)]
    public protected(set) ?string $verbatimCoordinates = null;

    #[Column(name: 'minimum_elevation_in_meters', type: 'string', nullable: true)]
    public protected(set) ?string $minimumElevationInMeters = null;

    #[Column(name: 'genus', type: 'string', nullable: true)]
    public protected(set) ?string $genus = null;

    #[Column(name: 'scientific_name', type: 'string', nullable: true)]
    public protected(set) ?string $scientificName = null;

    #[Column(name: 'specific_epithet', type: 'string', nullable: true)]
    public protected(set) ?string $specificEpithet = null;

    #[Column(name: 'scientific_name_authorship', type: 'string', nullable: true)]
    public protected(set) ?string $scientificNameAuthorship = null;

    private function convertCoordinateToDMS(float $decimal, string $positiveDirection, string $negativeDirection): array
    {
        $direction = $decimal >= 0 ? $positiveDirection : $negativeDirection;

        $decimal = abs($decimal);

        $degrees = (int) floor($decimal);

        $minutesFloat = ($decimal - $degrees) * 60;
        $minutes = (int) floor($minutesFloat);

        $seconds = round(($minutesFloat - $minutes) * 60, 1);

        return [
            $direction,
            $degrees,
            $minutes,
            $seconds,
        ];
    }

    public function getLatitudeDMS(): array
    {
        if (null === $this->decimalLatitude) {
            return [
                null,
                null,
                null,
                null,
            ];
        }

        return $this->convertCoordinateToDMS((float) $this->decimalLatitude, 'N', 'S');
    }

    public function getLongitudeDMS(): array
    {
        if (null === $this->decimalLongitude) {
            return [
                null,
                null,
                null,
                null,
            ];
        }

        return $this->convertCoordinateToDMS((float) $this->decimalLongitude, 'E', 'W');
    }

    public function __toString(): string
    {
        return json_encode([
            'catalogNumber' => $this->catalogNumber,
            'country' => $this->country,
            'county' => $this->county,
            'locality' => $this->locality,
            'continent' => $this->continent,
            'identifiedBy' => $this->identifiedBy,
            'dateIdentified' => $this->dateIdentified,
            'recordedBy' => $this->recordedBy,
            'stateProvince' => $this->stateProvince,
            'eventDate' => $this->eventDate,
            'verbatimEventDate' => $this->verbatimEventDate,
            'occurrenceRemarks' => $this->occurrenceRemarks,
            'decimalLatitude' => $this->decimalLatitude,
            'decimalLongitude' => $this->decimalLongitude,
            'verbatimCoordinates' => $this->verbatimCoordinates,
            'minimumElevationInMeters' => $this->minimumElevationInMeters,
            'genus' => $this->genus,
            'scientificName' => $this->scientificName,
            'specificEpithet' => $this->specificEpithet,
            'scientificNameAuthorship' => $this->scientificNameAuthorship,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }
}
