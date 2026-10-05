<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Database\Entity\Photos;

class VoucherVisionService
{
    public function prepareExcel(iterable $data): \XLSXWriter
    {
        $writer = new \XLSXWriter();

        if (!empty($data)) {
            $headers = [
                'ID' => 'string',
                'HerbNummer' => 'string',
                'collectionID' => 'string',
                'Collection' => 'string',
                'status' => 'string',
                'taxon' => 'string',
                'Sammler' => 'string',
                'series' => 'string',
                'series_number' => 'string',
                'Nummer' => 'string',
                'alt_number' => 'string',
                'Datum' => 'string',
                'Datum2' => 'string',
                'det' => 'string',
                'typified' => 'string',
                'typus' => 'string',
                'taxon_alt' => 'string',
                'nation_engl' => 'string',
                'provinz' => 'string',
                'Fundort' => 'string',
                'Fundort_engl' => 'string',
                'Habitat' => 'string',
                'Habitus' => 'string',
                'Bemerkungen' => 'string',
                'coord_NS' => 'string',
                'lat_degree' => 'string',
                'lat_minute' => 'string',
                'lat_second' => 'string',
                'coord_WE' => 'string',
                'long_degree' => 'string',
                'long_minute' => 'string',
                'long_second' => 'string',
                'exactness' => 'string',
                'quadrant' => 'string',
                'quadrant_sub' => 'string',
                'alt_min' => 'string',
                'alt_max' => 'string',
                'digital_image' => 'string',
                'digital_image_obs' => 'string',
                'observation' => 'string',
                'handwritten' => 'string',
                'specimenPID' => 'string',
            ];
            $writer->writeSheetHeader('Export', $headers);

            foreach ($data as $photo) {
                if (null === $photo->transcription) {
                    continue;
                }

                /** @var Photos $photo */
                $row = [
                    null,
                    $photo->getSpecimenIdFixedWidth(),
                    null,
                    null,
                    null,
                    $photo->transcription->scientificName,
                    $photo->transcription->recordedBy,
                    null,
                    null,
                    null,
                    null,
                    $photo->transcription->eventDate,
                    null,
                    $photo->transcription->identifiedBy,
                    null,
                    $photo->transcription->typus,
                    null,
                    $photo->transcription->country,
                    $photo->transcription->stateProvince,
                    null,
                    $photo->transcription->locality,
                    null,
                    null,
                    $photo->transcription->occurrenceRemarks,
                    ...$photo->transcription->getLatitudeDMS(),
                    ...$photo->transcription->getLongitudeDMS(),
                    null,
                    null,
                    null,
                    $photo->transcription->minimumElevationInMeters,
                    null,
                    '1',
                    null,
                    (string) $photo->transcription,
                    $photo->transcription->handwritten,
                    $photo->specimenPid,
                ];
                $writer->writeSheetRow('Export', $row);
            }
        }

        return $writer;
    }
}
