<?php declare(strict_types = 1);

namespace App\Grids;

use App\Facades\CuratorFacade;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Services\EntityServices\PhotoService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\UI\Control;
use Nette\Neon\Exception;
use Nette\Security\User;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class ImportedPhotosGrid extends Control
{

    private DataGrid $grid;

    public function __construct(protected readonly PhotoService $photoService, protected readonly BaseGridFactory $gridFactory, private CuratorFacade $curatorFacade, private readonly User $user)
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
        } catch (Exception $e) {
            $this->presenter->flashMessage($e->getMessage(), 'danger');
        }

        $this->redirect('this');
    }

    public function createComponentGrid(): DataGrid
    {
        $this->grid->setDataSource($this->defaultDatasource($this->user))->setDefaultSort(['id' => Criteria::DESC])->setRememberState(false);
        $this->grid->addColumnNumber('id', 'ID');
        $this->grid->addColumnDateTime('lastEditAt', 'processed at')->setFormat('d.m.Y H:i');
        $this->grid->addColumnNumber('specimen_id', 'Specimen')
            ->setRenderer(function (Photos $item) {
                $el = Html::el(null);
                $url = $this->presenter->link('Import:specimen', ['specimenNumericPartOfId' => $item->getSpecimenId()]);
                $el->addHtml('<a href="' . $url . '">' . $item->getFullSpecimenId() . '</a>');

                return $el;
            });
        $this->grid->addColumnNumber('jacq', 'JACQ')
            ->setRenderer(function (Photos $item) {
                $el = Html::el(null);
                $el->addHtml('<a href="https://' . $item->getHerbarium()->getAcronym() . '.jacq.org/' . $item->getHerbarium()->getAcronym() . $item->getSpecimenId() . '">JACQ</a>');

                return $el;
            });
        $this->grid->addColumnText('originalFilename', 'originalFilename')
            ->setFilterText();

        $this->grid->addColumnText('jp2Filename', 'jp2Filename')
            ->setFilterText();
        $this->grid->addColumnText('archiveFilename', 'archiveFilename')
            ->setFilterText();
        $this->grid->addColumnText('type', 'type')
            ->setRenderer(function (Photos $item) {
                $el = Html::el('i');
                $el->addHtml($item->getType()->getName());

                return $el;
            })
            ->setFilterSelect($this->curatorFacade->getAllPhotoTypes());
        $this->grid->addColumnNumber('width', 'width [px]');
        $this->grid->addColumnNumber('height', 'height [px]');
        $this->grid->addColumnNumber('archiveFileSize', 'archiveFileSize [B]');

        $this->grid->addExportCsvFiltered('Csv export (filtered)', 'curator_imported.csv')
            ->setTitle('Csv export (filtered)');

        $this->grid->addAction('delete', '', 'delete!')
            ->setIcon('trash')
            ->setTitle('Smazat')
            ->setClass('btn btn-xs btn-danger <strong class="text-danger">ajax</strong>')
            ->setConfirmation(
                new StringConfirmation('Do you really want to delete photo %s? This won\'t be allowed in production mode!', 'archiveFilename') // Second parameter is optional
            );

        return $this->grid;
    }

    protected function defaultDatasource(User $user): QueryBuilder
    {
        return $this->photoService->getDefaultDatasource($user)
            ->andWhere('p.status IN (:status)')
            ->setParameter('status', PhotosStatus::PASSED)
            ->orderBy('p.id', 'DESC');
    }

}
