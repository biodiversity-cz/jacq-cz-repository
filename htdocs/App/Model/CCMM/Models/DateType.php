<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents a date type with IRI and labels.
 */
class DateType extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'date_type';
    }
}

