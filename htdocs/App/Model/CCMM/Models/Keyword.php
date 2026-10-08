<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\TextWithLangBase;

/**
 * Represents a license with IRI and label.
 */
class Keyword extends TextWithLangBase
{
    public static function elementName(): string
    {
        return 'keyword';
    }
}
