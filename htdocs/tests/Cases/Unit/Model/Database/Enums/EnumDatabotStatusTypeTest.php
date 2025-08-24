<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Contact;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Enums\DatabotResultStatus;
use App\Model\Database\Enums\DatabotRole;
use App\Model\Database\Enums\EnumDatabotRoleType;
use App\Model\Database\Enums\EnumDatabotStatusType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('EnumDatabotStatusType converts values to/from PHP and DB', function (): void {
    $type = new EnumDatabotStatusType();

    $platform = \Mockery::mock(AbstractPlatform::class);

    Assert::same('enum_databot_result_status', $type->getName());
    Assert::true($type->requiresSQLCommentHint($platform));
    Assert::same('enum_databot_result_status', $type->getSQLDeclaration([], $platform));

    // PHP -> DB
    Assert::same('warning', $type->convertToDatabaseValue(DatabotResultStatus::WARNING, $platform));
    Assert::same('ok', $type->convertToDatabaseValue('ok', $platform));

    // DB -> PHP
    Assert::same(DatabotResultStatus::WARNING, $type->convertToPHPValue('warning', $platform));
});
