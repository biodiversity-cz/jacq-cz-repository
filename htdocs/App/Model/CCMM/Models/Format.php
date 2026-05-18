<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents a format with IRI and label.
 */
class Format extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'format';
    }
}
