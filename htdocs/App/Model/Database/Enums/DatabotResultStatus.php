<?php

declare(strict_types=1);

namespace App\Model\Database\Enums;

enum DatabotResultStatus: string
{
    case OK = 'ok';
    case ERROR = 'error';
    case WARNING = 'warning';
    case SKIPPED = 'skipped';
}
