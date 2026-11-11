<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Database\Entity\Photos;
use PhpOffice\PhpSpreadsheet\IOFactory;

class VoucherVisionService
{
    public function __construct(protected AppDir $appDir)
    {
    }

    public function prepareExcelJACQ(iterable $data): \XLSXWriter
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
                'Fundort_aggregated' => 'string',
                'link to image' => 'string',
                'hespi_warning' => 'string',
                'vv_warning' => 'string',
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
                    $photo->transcription->habitat,
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
                    $photo->transcription->locality.' '.$photo->transcription->habitat,
                    'https://herbarium.biodiversity.cz/admin/repository/photo/'.$photo->id,
                    $photo->transcriptionHespi->warning,
                    $photo->transcription->warning,
                ];
                $writer->writeSheetRow('Export', $row);
            }
        }

        return $writer;
    }

    public function prepareExcelMuseion(iterable $data, string $filename): void
    {
        $file = $this->appDir->getPath('Resources').DIRECTORY_SEPARATOR.'Museion_template.xlsx';
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $row = 5;

        foreach ($data as $photo) {
            /** @var Photos $photo */
            if (null === $photo->transcription) {
                continue;
            }

            $sheet->setCellValue('A'.$row, 'BO');
            $sheet->setCellValue('E'.$row, $photo->getSpecimenIdFixedWidth());
            $sheet->setCellValue('G'.$row, $photo->getSpecimenIdFixedWidth());
            $sheet->setCellValue('T'.$row, $photo->transcription->scientificName);
            $sheet->setCellValue('V'.$row, 1);
            $sheet->setCellValue('W'.$row, $photo->transcription->identifiedBy);
            $sheet->setCellValue('X'.$row, $photo->transcription->eventDate);
            $sheet->setCellValue('AA'.$row, $photo->transcription->country);

            $lat = $photo->transcription->getLatitudeDMS();
            $lon = $photo->transcription->getLongitudeDMS();

            $sheet->setCellValue('AB'.$row, $lat[0] ?? '');
            $sheet->setCellValue('AC'.$row, $lat[1] ?? '');
            $sheet->setCellValue('AD'.$row, $lat[2] ?? '');
            $sheet->setCellValue('AE'.$row, $lat[3] ?? '');
            $sheet->setCellValue('AF'.$row, $lon[0] ?? '');
            $sheet->setCellValue('AG'.$row, $lon[1] ?? '');
            $sheet->setCellValue('AH'.$row, $lon[2] ?? '');
            $sheet->setCellValue('AI'.$row, $lon[3] ?? '');
            $sheet->setCellValue('AP'.$row, $photo->transcription->minimumElevationInMeters);
            $sheet->setCellValue('AR'.$row, $photo->transcription->eventDate);
            $sheet->setCellValue('AU'.$row, $photo->transcription->typus);
            $sheet->setCellValue('CN'.$row, $photo->transcription->scientificName);
            $sheet->setCellValue('CW'.$row, $photo->transcription->locality);
            $sheet->setCellValue('CX'.$row, $photo->transcription->habitat);

            ++$row;
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filename);
    }
}
