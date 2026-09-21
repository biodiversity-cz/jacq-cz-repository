<?php

declare(strict_types=1);

namespace App\Model\CCMM\Models;

use App\Model\CCMM\Models\Base\IriLabelsBase;

/**
 * Represents a resource relation type with IRI and labels.
 */
class ResourceRelationType extends IriLabelsBase
{
    public static function elementName(): string
    {
        return 'resource_relation_type';
    }
}
