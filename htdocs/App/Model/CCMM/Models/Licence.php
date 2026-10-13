<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents a license with IRI and label.
 */
class Licence extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'licence';
    }
}
