<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents access rights with IRI and label.
 */
class Rights extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'rights';
    }
}
