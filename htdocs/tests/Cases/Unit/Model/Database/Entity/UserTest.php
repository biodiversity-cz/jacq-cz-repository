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
    Assert::same('johndoe', $user->username);

    $user->setPassword('secret');
    Assert::same('secret', $user->password);

    $user->setEmail('john@example.com');
    Assert::same('john@example.com', $user->email);

    $user->setName('John');
    Assert::same('John', $user->name);

    $user->setSurname('Doe');
    Assert::same('Doe', $user->surname);

    $user->setComment('Test comment');
    Assert::same('Test comment', $user->comment);

    Assert::true($user->active);
    $user->setActive(false);
    Assert::false($user->active);

    Assert::same('John Doe', $user->getFullname());

    // Mockované entity
    $herbarium = new Herbaria();

    $user->setLastVisitedHerbarium($herbarium);
    Assert::same($herbarium, $user->lastVisitedHerbarium);



    // --- Test TId trait ---
    $refId = new \ReflectionProperty($user, 'id');
    $refId->setAccessible(true);
    $refId->setValue($user, 42);
    Assert::same(42, $user->id);

    $clone = clone $user;
    Assert::null($refId->getValue($clone));
    Assert::exception(fn() => $clone->id, \TypeError::class);

    // --- Test TCreatedAt trait ---
    $user->setCreatedAt();
    $createdAt = $user->createdAt;
    Assert::type(\DateTimeImmutable::class, $createdAt);
    Assert::true($createdAt->getTimestamp() <= time());

    // --- Test TLastEditAt trait ---
    // Na začátku je null, protože je nullable a ještě nebylo nastaveno
    Assert::null((new \ReflectionProperty($user, 'lastEdit'))->getValue($user));

    // Nastavíme last edit (simulace PreUpdate)
    $user->setLastEditAt();
    $lastEditAt = $user->lastEdit;
    Assert::type(\DateTime::class, $lastEditAt);
    Assert::true($lastEditAt->getTimestamp() <= time());
});
