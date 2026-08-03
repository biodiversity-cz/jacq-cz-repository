<?php

declare(strict_types=1);

namespace App\Grids\Admin;

use App\Grids\BaseGridFactory;
use App\Model\Database\Entity\CetafSid;
use App\Services\EntityServices\CetafSidService;
use Contributte\Datagrid\Datagrid;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Control;
use Nette\Security\User;
use Nette\Utils\Html;

class CetafSidGrid extends Control
{
    private Datagrid $grid;

    public function __construct(protected readonly CetafSidService $cetafSidService, protected readonly BaseGridFactory $gridFactory, private readonly User $user)
    {
        $this->grid = $this->gridFactory->createBaseDatagrid();
    }

    public function create(): self
    {
        return $this;
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__.'/cetagSidGrid.latte');

        $template->render();
    }

    public function handleExportAll(): void
    {
        $qb = $this->defaultDatasource($this->user);
        $iterableResult = $qb->getQuery()->toIterable();

        $this->exportToXlsx($iterableResult);
    }

    public function createComponentGrid(): Datagrid
    {
        $this->grid->setDataSource($this->defaultDatasource($this->user))->setDefaultSort(['id' => 'DESC'])->setRememberState(false);

        $this->grid->addColumnNumber('id', 'repoID')
            ->setRenderer(function (CetafSid $item) {
                $el = Html::el(null);
                $url = $this->getPresenter()->link(':Front:Cetaf:object', ['id' => $item->id]);
                $el->addHtml('<a href="'.$url.'">'.$item->id.'</a>');

                return $el;
            });
        $this->grid->addColumnText('externalIdFromInstitution', 'technicalID');
        $this->grid->addColumnText('barcode', 'Barcode');
        $this->grid->addColumnNumber('decimalLatitude', 'Latitude')->setFormat(6);
        $this->grid->addColumnNumber('decimalLongitude', 'Longitude')->setFormat(6);
        $this->grid->addColumnText('recordedBy', 'recordedBy');
        $this->grid->addColumnText('occurrenceRemarks', 'remarks');
        $this->grid->addColumnText('eventDate', 'eventDate');
        $this->grid->addColumnText('verbatimEventDate', 'verbatimEventDate');
        $this->grid->addColumnText('locality', 'locality');
        $this->grid->addColumnText('verbatimElevation', 'elevation');
        $this->grid->addColumnText('previousIdentifications', 'previousIdentifications');
        $this->grid->addColumnText('identifiedBy', 'identifiedBy');
        $this->grid->addColumnText('dateIdentified', 'dateIdentified');
        $this->grid->addColumnText('scientificName', 'scientificName');

        $this->grid->addColumnDateTime('lastEditAt', 'lastEdit at (FROM - TO)')->setRenderer(function (CetafSid $item) {return $item->lastEdit->format('j. n. Y H:i'); })->setFilterDateRange('lastEdit', 'User registered:')->setFormat('j. n. Y', 'd. m. yyyy');

        $this->grid->addExportCsvFiltered('Csv export (filtered)', 'cetafsid_imported.csv')
            ->setTitle('Csv export (filtered)')
            ->setIcon('file-csv');

        $this->grid->addToolbarButton('exportAll', 'Export XLSX (all)')
            ->setClass('btn btn-xs btn-success')
            ->setIcon('file-excel')
            ->setTitle('Export všech záznamů')
        ;
        $this->grid->addExportCallback('Export XLSX (filtered)', function ($data): void {
            $this->exportToXlsx($data);
        }, true)
            ->setClass('btn btn-xs btn-info')
            ->setIcon('file-excel');

        return $this->grid;
    }

    protected function defaultDatasource(User $user): QueryBuilder
    {
        return $this->cetafSidService->getDefaultDatasource($user)
            ->orderBy('p.id', 'DESC');
    }

    private function exportToXlsx(iterable $data): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'export_').'.xlsx';
        $writer = new \XLSXWriter();

        if (!empty($data)) {
            $headers = ['id' => 'integer',
                'processed at' => 'datetime',
            ];
            $writer->writeSheetHeader('Export', $headers);

            foreach ($data as $sid) {
                /** @var CetafSid $sid */
                $row = [
                    $sid->id,
                    $sid->lastEdit->format('Y-m-d H:i:s'),
                ];
                $writer->writeSheetRow('Export', $row);
            }
        }

        $writer->writeToFile($filename);

        $this->getPresenter()->sendResponse(new FileResponse(
            $filename,
            'export.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ));
    }
}
