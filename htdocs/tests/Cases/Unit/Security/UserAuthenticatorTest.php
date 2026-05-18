<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Security;

use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\User;
use Tester\Assert;

require_once __DIR__.'/../../../bootstrap.php';

test('UserAuthenticator::authenticate throws exception when user not found', function (): void {
    $repo = \Mockery::mock(EntityRepository::class);
    $repo->shouldReceive('findOneByUsername')->with('john')->andReturn(null);

    $em = \Mockery::mock(EntityManagerInterface::class);
    $em->shouldReceive('getRepository')->andReturn($repo);

    $passwords = \Mockery::mock(Passwords::class); // nemusí mít žádné expectations

    $auth = new UserAuthenticator($em, $passwords);

    Assert::exception(function () use ($auth) {
        $auth->authenticate('john', 'secret');
    }, AuthenticationException::class, 'User not found.');
});

test('UserAuthenticator::authenticate throws exception for invalid password', function (): void {
    $user = \Mockery::mock(User::class);
    $user->shouldReceive('getPassword')->andReturn('hashedpassword');

    $repo = \Mockery::mock(EntityRepository::class);
    $repo->shouldReceive('findOneByUsername')->with('john')->andReturn($user);

    $em = \Mockery::mock(EntityManagerInterface::class);
    $em->shouldReceive('getRepository')->withAnyArgs()->andReturn($repo);

    $passwords = \Mockery::mock(Passwords::class);
    $passwords->shouldReceive('verify')->with('wrong', 'hashedpassword')->andReturn(false);

    $auth = new UserAuthenticator($em, $passwords);

    Assert::exception(function () use ($auth) {
        $auth->authenticate('john', 'wrong');
    }, AuthenticationException::class, 'Invalid password.');
});

test('UserAuthenticator::calculateHash returns hash for given password', function (): void {
    $passwords = \Mockery::mock(Passwords::class);
    $passwords->shouldReceive('hash')->with('mysecret')->andReturn('hashed-mysecret');

    $em = \Mockery::mock(EntityManagerInterface::class);
    $auth = new UserAuthenticator($em, $passwords);

    $hash = $auth->calculateHash('mysecret');
    Assert::equal('hashed-mysecret', $hash);
});

test('UserAuthenticator::calculateHash returns hash for default password when empty', function (): void {
    $passwords = \Mockery::mock(Passwords::class);
    $passwords->shouldReceive('hash')->with(UserAuthenticator::DEFAULT_PASSWORD)->andReturn('hashed-default');

    $em = \Mockery::mock(EntityManagerInterface::class);
    $auth = new UserAuthenticator($em, $passwords);

    $hash = $auth->calculateHash('');
    Assert::equal('hashed-default', $hash);
});

\Mockery::close();
