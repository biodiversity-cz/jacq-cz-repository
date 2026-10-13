<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents a conforms to standard with IRI and label.
 */
class ConformsToStandard extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'conforms_to_standard';
    }
}
