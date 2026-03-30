<?php declare(strict_types=1);

namespace App\Grids\Admin;

use App\Facades\CuratorFacade;
use App\Grids\BaseGridFactory;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Services\DatabotsResultService;
use App\Services\EntityServices\PhotoService;
use App\Services\Exceptions\ServiceException;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Contributte\Datagrid\Datagrid;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Control;
use Nette\Neon\Exception;
use Nette\Security\User;
use Nette\Utils\Html;

class ImportedPhotosGrid extends Control
{

    private DataGrid $grid;

    public function __construct(protected readonly PhotoService $photoService, protected readonly BaseGridFactory $gridFactory, private CuratorFacade $curatorFacade, private readonly User $user, protected DatabotsResultService $databotsService)
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
        $template->setFile(__DIR__ . '/importedPhotosGrid.latte');

        $template->render();
    }

    public function handleDelete(int $id): void
    {
        try {
            $photo = $this->photoService->getPhoto($this->user, $id);
            $this->curatorFacade->deletePhoto($this->user, $photo);
        } catch (ServiceException $exception){
            $this->presenter->flashMessage($exception->getMessage(), 'danger');
        }
        catch (Exception $e) {
            $this->presenter->flashMessage("It is not possible to delete the photo now, it has some unresolved \"duplicateTo\" relationship.", 'danger');
        }

        $this->redirect('this');
    }

    public function handleAddEmbargo(int $id): void
    {
        try {
            $photo = $this->photoService->getPhoto($this->user, $id);
            $this->curatorFacade->addEmbargoPhoto($this->user, $photo);
        } catch (ServiceException $exception){
            $this->presenter->flashMessage($exception->getMessage(), 'danger');
        }
        catch (Exception $e) {
            $this->presenter->flashMessage("It is not possible to put the emabrgo to the photo now", 'danger');
        }

        $this->redirect('this');
    }

    public function handleDropEmbargo(int $id): void
    {
        try {
            $photo = $this->photoService->getPhoto($this->user, $id);
            $this->curatorFacade->dropEmbargoPhoto($this->user, $photo);
        } catch (ServiceException $exception){
            $this->presenter->flashMessage($exception->getMessage(), 'danger');
        }
        catch (Exception $e) {
            $this->presenter->flashMessage("It is not possible to put the emabrgo to the photo now", 'danger');
        }

        $this->redirect('this');
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


        $this->grid->addColumnNumber('id', 'ID')
            ->setRenderer(function (Photos $item) {
                $el = Html::el(null);
                $url = $this->presenter->link('Repository:photo', ['id' => $item->id]);
                $el->addHtml('<a href="' . $url . '">' . $item->id . '</a>');

                return $el;
            });
        $this->grid->addColumnText('status', 'status')
            ->setRenderer(function (Photos $item) {
                $el = Html::el('i');
                $el->addHtml($item->status->name);
                if($item->status->id === PhotosStatus::EMBARGO){
                    $elInt = Html::el('span');
                    $elInt->addHtml(' (expires '.$item->embargoTimeout->format('d.m.Y') . ')');
                    $el->addHtml($elInt);
                }
                return $el;
            }) ->setFilterSelect($this->curatorFacade->getPassedStatuses());
        $this->grid->addColumnDateTime('createdAt', 'created at (FROM - TO)')->setRenderer(function (Photos $item){return $item->createdAt->format('j. n. Y H:i');})->setFilterDateRange( 'createdAt', 'User registered:')->setFormat('j. n. Y', 'd. m. yyyy');
        $this->grid->addColumnNumber('specimen_id', 'Specimen')
            ->setRenderer(function (Photos $item) {
                $el = Html::el(null);
                $url = $this->presenter->link('Repository:specimen', $item->specimenId);
                $el->addHtml('<a href="' . $url . '">' . $item->getFullSpecimenId() . '</a>');

                return $el;
            });
        $this->grid->addColumnText('originalFilename', 'originalFilename')
            ->setFilterText();

        $this->grid->addColumnText('type', 'type')
            ->setRenderer(function (Photos $item) {
                $el = Html::el('i');
                $el->addHtml($item->type->name);

                return $el;
            })
            ->setFilterSelect($this->curatorFacade->getAllPhotoTypes());
        $this->grid->addColumnNumber('width', 'width [px]');
        $this->grid->addColumnNumber('height', 'height [px]');
        $this->grid->addColumnNumber('archiveFileSize', 'archiveFileSize [B]');
//        $this->grid->addColumnNumber('qualityCheck', 'qualityCheck')
//            ->setRenderer(function (Photos $item) {
//                return $this->databotsService->getQualityEvaluation($item);
//            });


        $this->grid->addAction('delete', '', 'delete!')
            ->setIcon('trash')
            ->setTitle('Delete')
            ->setClass('btn btn-xs btn-danger')
            ->setConfirmation(
                new StringConfirmation('Do you really want to delete photo %s?', 'archiveFilename') // Second parameter is optional
            )
            ->setRenderCondition(function (Photos $item) {
                return in_array($item->status->id, PhotosStatus::DELETEABLE);
            });

        $this->grid->addAction('embargo', '', 'addEmbargo!')
            ->setIcon('clock')
            ->setTitle('Set embargo')
            ->setClass('btn btn-xs btn-warning')
            ->setConfirmation(
                new StringConfirmation('Do you really want to embargo photo %s? If already in embargo, the expiration interval will be restarted.', 'archiveFilename')
            )
            ->setRenderCondition(function (Photos $item) {
                return in_array($item->status->id, PhotosStatus::EMBARGOABLE);
            });

        $this->grid->addAction('dropEmbargo', '', 'dropEmbargo!')
            ->setIcon('clock-rotate-left')
            ->setTitle('Drop embargo')
            ->setClass('btn btn-xs btn-info')
            ->setConfirmation(
                new StringConfirmation('Do you really want to drop the embargo from photo %s?', 'archiveFilename')
            )
            ->setRenderCondition(function (Photos $item) {
                return $item->status->id === PhotosStatus::EMBARGO;
            });

        $this->grid->addExportCsvFiltered('Csv export (filtered)', 'curator_imported.csv')
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
        return $this->photoService->getDefaultDatasource($user)
            ->andWhere('p.status IN (:status)')
            ->setParameter('status', PhotosStatus::PASSED)
            ->orderBy('p.id', 'DESC');
    }

    private function exportToXlsx(iterable $data): void
    {
        $filename = tempnam(sys_get_temp_dir(), 'export_') . '.xlsx';
        $writer = new \XLSXWriter();

        if (!empty($data)) {

            $headers = ['id' => 'integer',
                'processed at' => 'datetime',
                'specimen' => 'string',
                'original filename' => 'string',
                'type' => 'string',
                'width' => 'integer',
                'height' => 'integer',
                'archive filesize' => 'integer'
            ];
            $writer->writeSheetHeader('Export', $headers);

            foreach ($data as $photo) {
                /** @var Photos $photo */
                $row = [
                    $photo->id,
                    $photo->createdAt->format('Y-m-d H:i:s'),
                    $photo->getFullSpecimenId(),
                    $photo->originalFilename,
                    $photo->type->name,
                    $photo->width,
                    $photo->height,
                    $photo->archiveFileSize
                ];
                $writer->writeSheetRow('Export', $row);
            }
        }

        $writer->writeToFile($filename);

        $this->presenter->sendResponse(new FileResponse(
            $filename,
            'export.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ));
    }


}
