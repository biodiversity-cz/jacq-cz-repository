<?php declare(strict_types=1);

namespace App\Model\Database\Enums;

enum DatabotRole: string
{
    case VALIDATOR = 'validator';
    case SCANNER = 'scanner';
    case EXPORTER = 'exporter';
}
