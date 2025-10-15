<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents a media type with IRI and label
 */
class MediaType extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'media_type';
     }
}
