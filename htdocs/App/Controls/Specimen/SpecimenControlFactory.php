<?php

declare(strict_types=1);

namespace App\Controls\Specimen;

interface SpecimenControlFactory
{
    public function create(): SpecimenControl;
}
