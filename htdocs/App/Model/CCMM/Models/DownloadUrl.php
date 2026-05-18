<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents a download URL with IRI and labels.
 */
class DownloadUrl extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'download_url';
    }
}
