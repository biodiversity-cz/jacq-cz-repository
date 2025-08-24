<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\UserRole;
use Tester\Assert;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('UserRole getters, setters and TId trait', function (): void {
    $role = new UserRole();

    $role->setName('admin');
    $role->setDescription('Administrator role');
    Assert::same('admin', $role->getName());
    Assert::same('Administrator role', $role->getDescription());

    Assert::same($role, $role->setName('user'));
    Assert::same('user', $role->getName());

    Assert::same($role, $role->setDescription('User role'));
    Assert::same('User role', $role->getDescription());

    Assert::same(1, UserRole::SUPER_ADMIN);
    Assert::same(2, UserRole::ADMIN);
    Assert::same(3, UserRole::USER);

    // --- Test TId trait ---

    // Nejprve testujeme, že $id je null při vytvoření (protected, proto Reflection)
    $refId = new \ReflectionProperty($role, 'id');
    $refId->setAccessible(true);
    $refId->setValue($role, 123); // simulujeme nastavené id (jako kdyby přišel z DB)

    // getId vrací id
    Assert::same(123, $role->getId());

    // Test klonování - id by se mělo nastavit na null
    $cloned = clone $role;
    Assert::null($refId->getValue($cloned));

    // getId() nyní na klonu vrací TypeError (protože id je null, ale getId(): int)
    // můžeme to otestovat
    Assert::exception(
        fn() => $cloned->getId(),
        \TypeError::class
    );
});
