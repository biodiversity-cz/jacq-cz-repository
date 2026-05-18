<?php

declare(strict_types=1);

namespace App\Controls\Image;

interface DetailControlFactory
{
    public function create(int $id): DetailControl;
}
