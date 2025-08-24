<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Security;

use App\Bootstrap;
use App\Model\Database\Entity\Herbaria;
use App\Security\Identity;
use App\Services\EntityServices\HerbariumService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mockery;
use Nette\Security\User;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

test('Identity::getFullname returns concatenated name and surname', function (): void {
    $identity = new Identity(123, null, [
        'name' => 'Jan',
        'surname' => 'Novák',
    ]);

    Assert::same('Jan Novák', $identity->getFullname());
});

test('Identity::getFullname returns empty parts as empty strings', function (): void {
    $identity = new Identity(123, null, []);

    Assert::same(' ', $identity->getFullname());

    $identity = new Identity(123, null, ['name' => 'Anna']);

    Assert::same('Anna ', $identity->getFullname());

    $identity = new Identity(123, null, ['surname' => 'Kovář']);

    Assert::same(' Kovář', $identity->getFullname());
});
