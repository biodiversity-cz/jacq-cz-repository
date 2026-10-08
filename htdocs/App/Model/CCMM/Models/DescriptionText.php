<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\TextWithLangBase;

class DescriptionText extends TextWithLangBase
{
    public static function elementName(): string
    {
        return 'description_text';
    }
}
