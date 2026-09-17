<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents an identifier scheme with IRI and label.
 */
class IdentifierScheme extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'scheme';
    }
}
