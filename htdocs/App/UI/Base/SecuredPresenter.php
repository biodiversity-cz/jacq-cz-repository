<?php declare(strict_types = 1);

namespace App\UI\Base;

use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\User;
use App\Security\Identity;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\UserService;

abstract class SecuredPresenter extends BasePresenter
{

    /** @inject */ public HerbariumService $herbariumService;
    /** @inject */ public UserService $userService;

    protected ?Herbaria $herbarium;
    protected ?User $userEntity;

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        if (!$this->user->isLoggedIn()) {
            $this->redirect(
                BasePresenter::DESTINATION_LOG_IN,
                ['backlink' => $this->storeRequest()]
            );
        }

        parent::checkRequirements($element);
    }

    public function startup(): void
    {
        $identity = $this->user->getIdentity();


        $this->userEntity = $this->userService->find($identity->id);
        if ($identity->getCurrentHerbariumId() !== null) {
            $this->herbarium = $this->herbariumService->find($identity->getCurrentHerbariumId());
            $this->template->herbarium = $this->herbarium;
        } else {


                // Handle users without herbarium (new OpenID users)
                // Redirect to a page where they can select/request a herbarium
                if (!$this->presenter->isLinkCurrent(':Admin:Herbarium:request')) {
                    $this->redirect(':Admin:Herbarium:request');
                }
        }

        parent::startup();
    }



}
