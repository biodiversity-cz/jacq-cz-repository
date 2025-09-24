<?php declare(strict_types = 1);

namespace App\UI\Front\Sign;

use App\Forms\FormFactory;
use App\Model\Database\Entity\User;
use App\Security\OpenIDAuthenticator;
use App\UI\Base\BasePresenter;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;

final class SignPresenter extends UnsecuredPresenter
{

    /**
     * @persistent
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    public $backlink;

    /** @inject  */ public FormFactory $formFactory;
    /** @inject  */ public OpenIDAuthenticator $openIDAuthenticator;

    public function actionIn(): void
    {
        if ($this->user->isLoggedIn()) {
            $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_IN);
        }
    }

    public function actionOut(): void
    {
        if ($this->user->isLoggedIn()) {
            // Get user's OpenID provider
            $identity = $this->user->getIdentity();
            $provider = $identity->data['openidProvider'] ?? null;

            if ($provider) {
                // Get user entity
                $userRepository = $this->entityManager->getRepository(User::class);
                $userEntity = $userRepository->find($identity->getId());

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

    public function actionOpenID(string $provider = 'orcid'): void
    {
        $config = $this->getOpenIDConfig($provider);

        try {
            $identity = $this->openIDAuthenticator->authenticate($provider, $config);
            $this->user->login($identity);

            if ($this->backlink !== null) {
                $this->restoreRequest($this->backlink);
            }

            $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_IN);
        } catch (\Exception $e) {
            $this->flashMessage('OpenID authentication failed: ' . $e->getMessage(), 'danger');
            $this->redirect('in');
        }
    }

    public function actionOpenIDCallback(string $provider): void
    {
        // This action handles the callback from OpenID providers
        $this->actionOpenID($provider);
    }

    private function getOpenIDConfig(string $provider): array
    {
        $config = $this->appConfiguration->getOpenIDProviders($provider);
        if (empty($config))  {
            throw new \InvalidArgumentException("OpenID provider '$provider' not configured");
        }
        $config['redirectUri'] = $this->link(':openid-callback');
        $config['signoutRedirectUri'] = $this->link(':out');

        return $config;
    }

    protected function createComponentLoginForm(): Form
    {
        // Keep the form for backward compatibility, but it won't be used for authentication
        $form = $this->formFactory->forFrontend();
        $form->addText('username')
            ->setRequired(true);
        $form->addPassword('password')
            ->setRequired(true);
        $form->addCheckbox('remember')
            ->setDefaultValue(true);
        $form->addSubmit('submit');
        $form->onSuccess[] = [$this, 'processLoginForm'];

        return $form;
    }

//    public function processLoginForm(Form $form): void
//    {
//        // This method is kept for backward compatibility but will show a message
//        $form->addError('Password authentication is disabled. Please use OpenID to sign in.');
//    }

    public function processLoginForm(Form $form): void
    {
        try {
            $this->getUser()->setExpiration($form->values->remember ? '14 days' : '20 minutes');
            $this->getUser()->login($form->values->username, $form->values->password);
        } catch (AuthenticationException $e) {
            $form->addError('Invalid credentials');

            return;
        }

        if ($this->backlink !== null) {

            $this->restoreRequest($this->backlink);
        }

        $this->redirect(BasePresenter::DESTINATION_AFTER_SIGN_IN);
    }
}
