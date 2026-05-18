<?php

declare(strict_types=1);

namespace App\Grids\Admin;

interface PublishedPhotosGridFactory
{
    public function create(): PublishedPhotosGrid;
}
