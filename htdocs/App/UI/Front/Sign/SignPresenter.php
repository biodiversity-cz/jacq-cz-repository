<?php

declare(strict_types=1);

namespace App\UI\Front\Sign;

use App\Forms\FormFactory;
use App\Model\Database\Entity\User;
use App\Security\OpenIDAuthenticator;
use App\UI\Base\BasePresenter;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\AbortException;

final class SignPresenter extends UnsecuredPresenter
{
    #[Nette\Application\Attributes\Persistent]
    public $backlink;

    /** @inject  */ public FormFactory $formFactory;
    /** @inject  */ public OpenIDAuthenticator $openIDAuthenticator;

    public function actionOut(): void
    {
        if ($this->user->isLoggedIn()) {
            // Get user's OpenID provider
            $identity = $this->user->getIdentity();
            $provider = $identity->data['openidProvider'] ?? null;

            if ($provider) {
                // Get user entity
                $userRepository = $this->entityManager->getRepository(User::class);
                $userEntity = $userRepository->find($identity->id);

                if ($userEntity) {
                    // Get provider config
                    $config = $this->getOpenIDConfig($provider);

                    // Perform OpenID sign out
                    $logoutUrl = $this->openIDAuthenticator->signOut($userEntity, $config);

                    // Log out locally
                    $this->user->logout();

                    // Redirect to provider logout if available
                    if ($logoutUrl) {
                        $this->redirectUrl($logoutUrl);

                        return;
                    }
                }
            }

            // Standard logout if no OpenID provider or error
            $this->user->logout();
        }

        $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_OUT);
    }

    public function actionIn(): void
    {
        $config = $this->getOpenIDConfig('cesnet');

        try {
            $identity = $this->openIDAuthenticator->authenticate($config);
            $this->user->login($identity);

            if (null !== $this->backlink) {
                $this->restoreRequest($this->backlink);
            }

            $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_IN);
        } catch (AbortException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function actionOpenIdCallback(): void
    {
        // This action handles the callback from OpenID providers
        $this->actionIn();
    }

    private function getOpenIDConfig(string $provider): array
    {
        $config = $this->appConfiguration->getOpenIDProviders($provider);
        if (empty($config)) {
            throw new \InvalidArgumentException("OpenID provider '$provider' not configured");
        }
        $config['redirectUri'] = $this->link(':openid-callback');
        $config['signoutRedirectUri'] = $this->link(':out');

        return $config;
    }
}
