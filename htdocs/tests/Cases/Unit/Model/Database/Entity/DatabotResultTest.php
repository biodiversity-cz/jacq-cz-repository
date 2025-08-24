<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\Databot;
use App\Model\Database\Entity\DatabotResult;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Enums\DatabotResultStatus;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';


test('DatabotResult entity getters and setters', function (): void {
    $result = new DatabotResult();

    // Reflection pro protected vlastnosti z traitů
    $refId = new \ReflectionProperty($result, 'id');
    $refId->setAccessible(true);
    $refCreatedAt = new \ReflectionProperty($result, 'createdAt');
    $refCreatedAt->setAccessible(true);

    $refId->setValue($result, 123);
    $refCreatedAt->setValue($result, new \DateTimeImmutable('2025-08-06T10:00:00+00:00'));

    Assert::same(123, $result->getId());
    Assert::equal(new \DateTimeImmutable('2025-08-06T10:00:00+00:00'), $result->getCreatedAt());

    // Nastavení a kontrola Databot
    $databot = new Databot();
    $result->setDatabot($databot);
    Assert::same($databot, $result->getDatabot());

    // Nastavení a kontrola Photos
    $photo = new Photos();
    $result->setPhoto($photo);
    Assert::same($photo, $result->getPhoto());

    // Nastavení a kontrola statusu
    $result->setStatus(DatabotResultStatus::OK);
    Assert::same(DatabotResultStatus::OK, $result->getStatus());

    $result->setStatus(DatabotResultStatus::ERROR);
    Assert::same(DatabotResultStatus::ERROR, $result->getStatus());

    // Nastavení a kontrola zprávy
    $result->setMessage('Test message');
    Assert::same('Test message', $result->getMessage());

    $result->setMessage(null);
    Assert::null($result->getMessage());

    // Nastavení a kontrola resultData
    $data = ['key' => 'value', 'number' => 42];
    $result->setResultData($data);
    Assert::same($data, $result->getResultData());

    $result->setResultData(null);
    Assert::null($result->getResultData());
});
