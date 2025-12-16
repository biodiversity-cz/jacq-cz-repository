<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Contact;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Enums\DatabotRole;
use App\Model\Database\Enums\EnumDatabotRoleType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';



test('EnumDatabotRole conversion and metadata behavior', function (): void {
    $type = new EnumDatabotRoleType();
    $platform = \Mockery::mock(AbstractPlatform::class);

    Assert::same('databots.enum_databot_role', $type->name);
    Assert::true($type->requiresSQLCommentHint($platform));
    Assert::same('databots.enum_databot_role', $type->getSQLDeclaration([], $platform));

    // PHP -> DB
    Assert::same('validator', $type->convertToDatabaseValue(DatabotRole::VALIDATOR, $platform));
    Assert::same('validator', $type->convertToDatabaseValue('validator', $platform));

    // DB -> PHP
    Assert::same(DatabotRole::VALIDATOR, $type->convertToPHPValue('validator', $platform));
});
