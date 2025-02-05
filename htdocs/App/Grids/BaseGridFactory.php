<?php declare(strict_types=1);

namespace App\Grids;

use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;

abstract class BaseGridFactory
{

    public const array FILTER_NOTHING = ['' => ' - - - - - '];
    protected Presenter $presenter;
    protected DataGrid $grid;

    public function createBaseDatagrid($name): DataGrid
    {
        $this->grid = new DataGrid($this->presenter, $name);
        $this->grid
            ->setItemsPerPageList([10, 50, 200])
            ->setStrictSessionFilterValues(false);
//        DataGrid::$iconPrefix = 'bi bi-';
        return $this->grid;
    }

}
