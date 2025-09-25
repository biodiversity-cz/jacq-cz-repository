<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Model\Database\Entity\UserRole;
use Tester\Assert;
use App\Model\Database\Entity\User;
use App\Model\Database\Entity\Herbaria;

require_once __DIR__ . '/../../../../../bootstrap.php';

test('User entity with TId, TCreatedAt and TLastEditAt traits', function (): void {
    $user = new User();

    // Setters/getters základních scalar hodnot
    $user->setUsername('johndoe');
    Assert::same('johndoe', $user->getUsername());

    $user->setPassword('secret');
    Assert::same('secret', $user->getPassword());

    $user->setEmail('john@example.com');
    Assert::same('john@example.com', $user->getEmail());

    $user->setName('John');
    Assert::same('John', $user->getName());

    $user->setSurname('Doe');
    Assert::same('Doe', $user->getSurname());

    $user->setComment('Test comment');
    Assert::same('Test comment', $user->getComment());

    Assert::true($user->isActive());
    $user->setActive(false);
    Assert::false($user->isActive());

    Assert::same('John Doe', $user->getFullname());

    // Mockované entity
    $herbarium = new Herbaria();

    $user->setLastVisitedHerbarium($herbarium);
    Assert::same($herbarium, $user->getLastVisitedHerbarium());



    // --- Test TId trait ---
    $refId = new \ReflectionProperty($user, 'id');
    $refId->setAccessible(true);
    $refId->setValue($user, 42);
    Assert::same(42, $user->getId());

    $clone = clone $user;
    Assert::null($refId->getValue($clone));
    Assert::exception(fn() => $clone->getId(), \TypeError::class);

    // --- Test TCreatedAt trait ---
    $user->setCreatedAt();
    $createdAt = $user->getCreatedAt();
    Assert::type(\DateTimeImmutable::class, $createdAt);
    Assert::true($createdAt->getTimestamp() <= time());

    // --- Test TLastEditAt trait ---
    // Na začátku je null, protože je nullable a ještě nebylo nastaveno
    Assert::null((new \ReflectionProperty($user, 'lastEdit'))->getValue($user));

    // Nastavíme last edit (simulace PreUpdate)
    $user->setLastEditAt();
    $lastEditAt = $user->getLastEditAt();
    Assert::type(\DateTime::class, $lastEditAt);
    Assert::true($lastEditAt->getTimestamp() <= time());
});
