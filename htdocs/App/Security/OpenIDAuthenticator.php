<?php
declare(strict_types=1);

namespace App\Security;

use App\Model\Database\Entity\User;
use App\Model\Database\Entity\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Jumbojett\OpenIDConnectClient;

final class OpenIDAuthenticator
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function authenticate(string $provider, array $config): Identity
    {
        $oidc = new OpenIDConnectClient(
            $config['issuer'] ?? null,
            $config['clientId'],
            $config['clientSecret']
        );

        $oidc->setRedirectURL($config['redirectUri']);

        // Set up scopes
        $oidc->addScope(['openid', 'profile', 'email']);

        // Authenticate
        $oidc->authenticate();

        // Get user info
        $userInfo = $oidc->requestUserInfo();
        $subject = $oidc->getSubject();
        $idToken = $oidc->getIdToken();
        $refreshToken = $oidc->getRefreshToken();

        // Find or create user
        $user = $this->findOrCreateUser($subject, $provider, $userInfo, $idToken, $refreshToken);

        return new Identity($user, $this->entityManager);
    }

    public function signOut(User $user, array $config): string
    {
        // Clear user's OpenID tokens
        $user->setOpenidIdToken(null)
            ->setOpenidRefreshToken(null);

        $this->entityManager->flush();

        // Generate OpenID logout URL if supported by provider
        $logoutUrl = null;

        switch ($user->getOpenidProvider()) {
            case 'google':
                // Google doesn't support RP-initiated logout, just return home URL
                $logoutUrl = 'https://accounts.google.com/logout';
                break;
            case 'keycloak':
                // Keycloak supports RP-initiated logout
                $logoutUrl = rtrim($config['baseUrl'], '/') . '/realms/' . $config['realm'] .
                    '/protocol/openid-connect/logout?redirect_uri=' . urlencode($config['signoutRedirectUri']);
                break;
        }

        return $logoutUrl;
    }

    private function findOrCreateUser(string $subject, string $provider, object $userInfo, string $idToken, ?string $refreshToken): User
    {
        // Try to find existing user by OpenID subject and provider
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['openidSubject' => $subject, 'openidProvider' => $provider]);

        if ($user) {
            // Update user info and tokens if needed
            $user->setName($userInfo->given_name ?? '')
                ->setSurname($userInfo->family_name ?? '')
                ->setEmail($userInfo->email ?? '')
                ->setOpenidIdToken($idToken)
                ->setOpenidRefreshToken($refreshToken);

            $this->entityManager->flush();
            return $user;
        }

        // Try to find existing user by email
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $userInfo->email ?? '']);

        if ($user) {
            // Link existing user to OpenID
            $user->setOpenidSubject($subject)
                ->setOpenidProvider($provider)
                ->setOpenidIdToken($idToken)
                ->setOpenidRefreshToken($refreshToken);

            $this->entityManager->flush();
            return $user;
        }

        // Create new user
        $user = new User();
        $user->setUsername($userInfo->email ?? uniqid('openid_'))
            ->setPassword('') // No password for OpenID users
            ->setName($userInfo->given_name ?? '')
            ->setSurname($userInfo->family_name ?? '')
            ->setEmail($userInfo->email ?? '')
            ->setOpenidSubject($subject)
            ->setOpenidProvider($provider)
            ->setOpenidIdToken($idToken)
            ->setOpenidRefreshToken($refreshToken)
            ->setActive(true);

        // Assign default role (guest for new users)
        $role = $this->entityManager->getRepository(UserRole::class)->find(UserRole::USER);
        if ($role) {
            // For new users, we need to assign a role to a herbarium
            // For now, we'll just leave this for later assignment
        }

        // No herbarium for new users (will be assigned later)
        $user->setLastVisitedHerbarium(null);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
