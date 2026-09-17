<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents a role with IRI and label.
 */
class Role extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'role';
    }
}
