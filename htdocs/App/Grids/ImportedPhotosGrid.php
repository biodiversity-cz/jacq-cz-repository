<?php declare(strict_types = 1);

namespace App\Grids;

use App\Model\Database\Entity\Photos;
use App\Model\Database\Entity\PhotosStatus;
use App\Model\Entity\Accession;
use App\Services\EntityServices\PhotoService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Nette\Application\UI\Presenter;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

class ImportedPhotosGrid extends BaseGridFactory
{

    public function __construct(protected readonly PhotoService $photoService)
    {
    }

    protected function defaultDatasource(): QueryBuilder
    {
        return $this->photoService->getDefaultDatasource()
            ->andWhere('a.status IN (:status)')
            ->setParameter('status', PhotosStatus::PASSED)
            ->orderBy('a.id', 'DESC');
    }

    public function create(Presenter $presenter, string $name): DataGrid
    {
        $this->presenter = $presenter;
        $this->createBaseDatagrid($name);
        $this->grid->setDataSource($this->defaultDatasource())->setDefaultSort(["id" => Criteria::DESC])->setRememberState(false);
        $this->grid->addColumnNumber('id', 'ID');
        $this->grid->addColumnDateTime('lastEditAt', 'processed at')->setFormat('d.m.Y H:i');
        $this->grid->addColumnNumber('specimen_id', 'Specimen')
            ->setRenderer(function ($item) {
                $el = Html::el(null);
                /** @var Photos $item */
                $url = $this->presenter->link(':Front:Repository:specimen', ['specimenFullId' => $item->getFullSpecimenId()]);
                $el->addHtml('<a href="' . $url . '">' . $item->getFullSpecimenId() . '</a>');
                return $el;
            });
        $this->grid->addColumnNumber('jacq', 'JACQ')
            ->setRenderer(function ($item) {
                $el = Html::el(null);
                /** @var Photos $item */
                $url = $this->presenter->link(':Front:Repository:specimen', ['specimenFullId' => $item->getFullSpecimenId()]);
                $el->addHtml('<a href="https://'.$item->getHerbarium()->getAcronym().'.jacq.org/'.$item->getHerbarium()->getAcronym().$item->getSpecimenId(). '">JACQ</a>');
                return $el;
            });
        $this->grid->addColumnText('originalFilename', 'originalFilename')
            ->setFilterText();

        $this->grid->addColumnText('jp2Filename', 'jp2Filename')
            ->setFilterText();
        $this->grid->addColumnText('archiveFilename', 'archiveFilename')
            ->setFilterText();

        $this->grid->addColumnNumber('width', 'width [px]');
        $this->grid->addColumnNumber('height', 'height [px]');
        $this->grid->addColumnNumber('archiveFileSize', 'archiveFileSize [B]');

        $this->grid->addExportCsvFiltered('Csv export (filtered)', 'curator_imported.csv')
            ->setTitle('Csv export (filtered)');
        return $this->grid;
    }

}
