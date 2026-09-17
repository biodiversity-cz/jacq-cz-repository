<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;
use App\Model\CCMM\Traits\XmlSerializableTrait;
use App\Model\CCMM\XmlSerializable;

/**
 * Represents a license with IRI and label.
 */
class License extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'license';
    }
}
