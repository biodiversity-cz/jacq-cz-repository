<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents a description type with IRI and label.
 */
class DescriptionType extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'description_type';
    }
}
