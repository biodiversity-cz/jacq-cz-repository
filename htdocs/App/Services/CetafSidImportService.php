<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ImportValuesException;
use App\Model\Database\Entity\CetafSid;
use App\Model\Database\Entity\Herbaria;
use App\Services\EntityServices\CetafSidService;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Http\FileUpload;
use Nette\Http\Session;
use Nette\Security\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CetafSidImportService
{
    public const array expectedHeaders = ['id', 'basisOfRecord', 'occurrenceID', 'recordedBy', 'occurrenceRemarks', 'eventDate', 'locality', 'verbatimElevation', 'decimalLatitude', 'decimalLongitude', 'verbatimIdentification', 'identifiedBy', 'dateIdentified', 'scientificName', 'verbatimEventDate'];

    protected const int dataStartingRow = 2;
    public const array colNames = [
        'id' => 0,
        'basisOfRecord' => 1,
        'occurrenceID' => 2,
        'recordedBy' => 3,
        'occurrenceRemarks' => 4,
        'eventDate' => 5,
        'locality' => 6,
        'verbatimElevation' => 7,
        'decimalLatitude' => 8,
        'decimalLongitude' => 9,
        'verbatimIdentification' => 10,
        'identifiedBy' => 11,
        'dateIdentified' => 12,
        'scientificName' => 13,
        'verbatimEventDate' => 14,
    ];

    public const string SESSION_SECTION = 'importCetaf';

    protected ?Worksheet $worksheet = null;

    protected array $errors = [];

    protected Herbaria $herbarium;

    public function __construct(protected readonly User $user, protected Session $session, protected readonly EntityManagerInterface $entityManager, protected CetafSidService $service)
    {
        $this->herbarium = $this->entityManager->getRepository(Herbaria::class)->find($this->user->getIdentity()->getCurrentHerbariumId());
    }

    public function validate($formData): int
    {
        $this->session->getSection(self::SESSION_SECTION)->remove();
        $this->loadExcel($formData);
        $this->checkHeader();
        $rowIndex = self::dataStartingRow;
        while (true) {
            $row = $this->worksheet->rangeToArray('A'.$rowIndex.':'.$this->lastColumn().$rowIndex)[0];
            if (count(array_filter($row)) > 0) {
                $this->validateRow($row, $rowIndex);
            } else {
                break;
            }
            ++$rowIndex;
        }
        if (count($this->errors) > 0) {
            $storage = $this->session->getSection(self::SESSION_SECTION);
            $storage->set('errors', $this->errors);
            throw new ImportValuesException('Invalid value(s), please revise your import file.');
        }

        return $rowIndex - 1;
    }

    public function import($formData)
    {
        $this->validate($formData);
        $rowIndex = self::dataStartingRow;
        $this->entityManager->beginTransaction();
        while (true) {
            $row = $this->worksheet->rangeToArray('A'.$rowIndex.':'.$this->lastColumn().$rowIndex)[0];
            if (count(array_filter($row)) > 0) {
                $this->importRow($row);
            } else {
                break;
            }
            ++$rowIndex;
        }
        $this->entityManager->commit();
        $this->entityManager->flush();
    }

    /**
     * generates Excel column names A, B, ..., Z, AA, AB, ..., AY eg.
     */
    protected function generateColumnNamesRange($columnCount): array
    {
        $columns = [];
        for ($i = 0; $i < $columnCount; ++$i) {
            $letter = '';
            $temp = $i;
            do {
                $letter = chr(65 + ($temp % 26)).$letter;
                $temp = intdiv($temp, 26) - 1;
            } while ($temp >= 0);
            $columns[] = strtolower($letter);
        }

        return $columns;
    }

    protected function loadExcel($formData): Worksheet
    {
        /** @var FileUpload $file */
        $file = $formData->table;
        $spreadsheet = IOFactory::load($file->getTemporaryFile());
        $this->worksheet = $spreadsheet->getActiveSheet();

        return $this->worksheet;
    }

    protected function checkHeader(): void
    {
        $firstRow = $this->worksheet->rangeToArray('A1:'.$this->lastColumn().'1')[0];
        $headers = array_map('strtolower', $firstRow);

        $expectedHeaders = array_map('strtolower', self::expectedHeaders);

        if ($headers !== $expectedHeaders) {
            throw new ImportValuesException('File does not contain headers (the first row) in expected format.');
        }
    }

    protected function lastColumn(): string
    {
        $cols = $this->generateColumnNamesRange(count(self::expectedHeaders));

        return end($cols);
    }

    protected function validateRow(array $row, int $rowIndex): void
    {
        $value = $row[self::colNames['occurrenceID']];
        if (empty($value)) {
            $this->errors[$rowIndex][] = [
                'column' => self::expectedHeaders[self::colNames['occurrenceID']],
                'value' => $value,
            ];
        }
        $value = $row[self::colNames['id']];
        if (empty($value)) {
            $this->errors[$rowIndex][] = [
                'column' => self::expectedHeaders[self::colNames['id']],
                'value' => $value,
            ];
        }

        // TODO add validation of barcode - that it fits to the herbarium acronym..
    }

    protected function importRow(array $row): void
    {
        $sid = $this->service->findOneBy(['externalIdFromInstitution' => $row[self::colNames['id']], 'herbarium' => $this->herbarium]);

        if (null === $sid) {
            $sid = new CetafSid()
                ->setHerbarium($this->herbarium)
                ->setExternalIdFromInstitution($row[self::colNames['id']])
                ->setBarcode($row[self::colNames['occurrenceID']]);
            $this->entityManager->persist($sid);
        }
        $sid
            ->setScientificName($row[self::colNames['scientificName']])
            ->setDecimalLatitude($row[self::colNames['decimalLatitude']])
            ->setDecimalLongitude($row[self::colNames['decimalLongitude']])
            ->setRecordedBy($row[self::colNames['recordedBy']])
            ->setOccurrenceRemarks($row[self::colNames['occurrenceRemarks']])
            ->setEventDate($row[self::colNames['eventDate']])
            ->setVerbatimEventDate($row[self::colNames['verbatimEventDate']])
            ->setLocality($row[self::colNames['locality']])
            ->setVerbatimElevation($row[self::colNames['verbatimElevation']])
            ->setIdentifiedBy($row[self::colNames['identifiedBy']])
            ->setDateIdentified($row[self::colNames['dateIdentified']])
            ->setPreviousIdentifications($row[self::colNames['verbatimIdentification']])
            ->setCreatedAt()
            ->setLastEditAt();
    }

    public function getErrors()
    {
        return $this->session->getSection(self::SESSION_SECTION)->get('errors');
    }
}
