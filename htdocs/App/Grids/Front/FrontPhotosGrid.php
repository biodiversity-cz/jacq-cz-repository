<?php

declare(strict_types=1);

namespace App\Grids\Front;

use App\Facades\CuratorFacade;
use App\Grids\BaseGridFactory;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Services\DatabotsResultService;
use App\Services\EntityServices\PhotoService;
use Contributte\Datagrid\Datagrid;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\UI\Control;
use Nette\Utils\Html;

class FrontPhotosGrid extends Control
{
    private Datagrid $grid;

    public function __construct(protected readonly PhotoService $photoService, protected readonly BaseGridFactory $gridFactory, private CuratorFacade $curatorFacade, protected DatabotsResultService $databotsService)
    {
        $this->grid = $this->gridFactory->createBaseDatagrid();
    }

    protected function getHerbarium(): \App\Model\Database\Entity\Herbaria
    {
        $herbarium = $this->getPresenter()->herbarium;
        if (null === $herbarium) {
            throw new \RuntimeException('Herbarium is not set in the presenter.');
        }

        return $herbarium;
    }

    public function create(): self
    {
        return $this;
    }

    public function render(): void
    {
        $template = $this->template;
        $template->setFile(__DIR__.'/frontPhotosGrid.latte');

        $template->render();
    }

    public function createComponentGrid(): Datagrid
    {
        $this->grid->setDataSource($this->defaultDatasource())->setDefaultSort(['id' => 'DESC'])->setRememberState(false);

        $this->grid->addColumnNumber('id', 'ID')
            ->setRenderer(function (Photos $item) {
                $el = Html::el(null);
                $url = $this->presenter->link('Repository:image', ['id' => $item->id]);
                $el->addHtml('<a href="'.$url.'">'.$item->id.'</a>');

                return $el;
            });

        $this->grid->addColumnNumber('specimen_id', 'Specimen')
            ->setRenderer(function (Photos $item) {
                $el = Html::el(null);
                $url = $this->presenter->link('Repository:specimen', $item->getFullSpecimenId());
                $el->addHtml('<a href="'.$url.'">'.$item->getFullSpecimenId().'</a>');

                return $el;
            });

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

        return $this->grid;
    }

    protected function defaultDatasource(): QueryBuilder
    {
        return $this->photoService->getAllPublishedPhotosDatasource()
            ->andWhere('p.status IN (:status)')
            ->andWhere('p.herbarium = :herbarium')
            ->setParameter('herbarium', $this->getHerbarium()->id)
            ->setParameter('status', PhotosStatus::PUBLISHED)
            ->orderBy('p.id', 'DESC');
    }
}
