<?php

declare(strict_types=1);

namespace App\Grids;

use Contributte\Datagrid\Datagrid;

class BaseGridFactory
{
    public const array FILTER_NOTHING = ['' => ' - - - - - '];

    protected Datagrid $grid;

    public function createBaseDatagrid(): Datagrid
    {
        $this->grid = new Datagrid();
        $this->grid
            ->setItemsPerPageList([10, 50, 200])
            ->setStrictSessionFilterValues(false);

        //        DataGrid::$iconPrefix = 'bi bi-';
        return $this->grid;
    }
}
