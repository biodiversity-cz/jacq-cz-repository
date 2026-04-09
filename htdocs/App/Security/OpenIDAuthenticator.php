<?php
declare(strict_types=1);

namespace App\Security;

use App\Model\Database\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Jumbojett\OpenIDConnectClient;
use Nette\Application\LinkGenerator;

final class OpenIDAuthenticator
{
    public function __construct(
        private EntityManagerInterface $entityManager, private LinkGenerator $linkGenerator
    )
    {
    }

    public function authenticate(array $config): Identity
    {
        $oidc = new OpenIDConnectClient(
            $config['issuer'] ?? null,
            $config['clientId'],
            $config['clientSecret']
        );

        $oidc->setRedirectURL($this->linkGenerator->link('Front:Sign:open-id-callback'));

        // Set up scopes
        $scopesString = $config['scopes'];
        $scopesArray = explode(' ', $scopesString);
        $oidc->addScope($scopesArray);

        //TODO enable PKCE - for e-infra facility
//        $oidc->setCodeChallengeMethod('S256');

        // Authenticate
        $oidc->authenticate();

        // Get user info
        $subject = $oidc->requestUserInfo('sub');
        $userInfo = $oidc->requestUserInfo();
        $idToken = $oidc->getIdToken();
        $refreshToken = $oidc->getRefreshToken();

        // Find or create user
        $user = $this->findOrCreateUser($subject, $userInfo, $idToken, $refreshToken);

        return new Identity($user);
    }

    public function signOut(User $user, array $config): string
    {
        // Clear user's OpenID tokens
        $user->setOpenidIdToken(null)
            ->setOpenidRefreshToken(null);

        $this->entityManager->flush();

        // Generate OpenID logout URL if supported by provider
        $logoutUrl = null;

        switch ($user->openidProvider) {
            case 'keycloak':
                // Keycloak supports RP-initiated logout
                $logoutUrl = rtrim($config['baseUrl'], '/') . '/realms/' . $config['realm'] .
                    '/protocol/openid-connect/logout?redirect_uri=' . urlencode($config['signoutRedirectUri']);
                break;
        }

        return $logoutUrl;
    }

    private function findOrCreateUser(string $subject,   object $userInfo, string $idToken, ?string $refreshToken): User
    {
        // Try to find existing user by OpenID subject and provider
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['openidSubject' => $subject, 'openidProvider' => 'cesnet']);

        if ($user) {
            $user->setName($userInfo->given_name ?? '')
                ->setSurname($userInfo->family_name ?? '')
                ->setEmail($userInfo->email ?? '')
                ->setOpenidIdToken($idToken)
                ->setOpenidRefreshToken($refreshToken)
                ->initializeCurrentHerbarium();

            $this->entityManager->flush();
            return $user;
        }

        // Try to find existing user by email = Link existing user to OpenID
        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $userInfo->email ?? '']);

        if ($user) {
            $user->setOpenidSubject($subject)
                ->setOpenidProvider('cesnet')
                ->setOpenidIdToken($idToken)
                ->setOpenidRefreshToken($refreshToken)
                ->initializeCurrentHerbarium();

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
            ->setOpenidProvider('cesnet')
            ->setOpenidIdToken($idToken)
            ->setOpenidRefreshToken($refreshToken)
            ->setActive(false)
            ->setCreatedAt()
            ->setLastEditAt();

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
