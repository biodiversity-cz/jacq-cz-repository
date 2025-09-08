<?php declare(strict_types=1);

namespace App\Model\Database\Enums;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class EnumDatabotStatusType extends Type
{
    public const NAME = 'databots.' . 'enum_databot_result_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return self::NAME;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return DatabotResultStatus::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return $value instanceof DatabotResultStatus ? $value->value : $value;
    }

}
