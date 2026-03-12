<?php declare(strict_types = 1);

namespace App\Grids\Front;

use App\Model\Database\Entity\Herbaria;

interface FrontPhotosGridFactory
{

    public function create(Herbaria $herbarium): FrontPhotosGrid;

}
