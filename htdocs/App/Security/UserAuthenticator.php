<?php

declare(strict_types=1);

namespace App\Security;

use App\Model\Database\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\Passwords;

final class UserAuthenticator implements Authenticator
{
    public const string DEFAULT_PASSWORD = 'Trogoderma2024';

    public function __construct(private EntityManagerInterface $entityManager, private Passwords $passwords)
    {
    }

    public function authenticate(string $username, string $password): Identity
    {
        $row = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
        if (!$row) {
            throw new AuthenticationException('User not found.');
        }

        if (!$this->passwords->verify($password, $row->getPassword())) {
            throw new AuthenticationException('Invalid password.');
        }

        return new Identity($row);
    }

    /**
     * Computes default password hash.
     */
    public function calculateHash(string $password = ''): string
    {
        if ('' === $password) {
            return $this->calculateHash(self::DEFAULT_PASSWORD);
        }

        return $this->passwords->hash($password);
    }
}
