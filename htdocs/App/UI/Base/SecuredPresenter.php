<?php declare(strict_types = 1);

namespace App\UI\Base;

use App\Model\Database\Entity\Herbaria;
use App\Security\Identity;
use App\Services\EntityServices\HerbariumService;

abstract class SecuredPresenter extends BasePresenter
{

    /** @inject */ public HerbariumService $herbariumService;

    protected ?Herbaria $herbarium;

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

        // If we have an EnhancedIdentity, use its method
        if ($identity instanceof Identity) {
            $this->herbarium = $this->herbariumService->find($identity->getCurrentHerbariumId());
            $this->template->herbarium = $this->herbarium;
        } else {
            // Fallback to the old method
            $herbariumId = $identity->data['lastVisitedHerbarium'] ?? null;

            if ($herbariumId) {
                $this->herbarium = $this->herbariumService->getCurrentUserHerbarium($this->user);
                $this->template->herbarium = $this->herbarium;
            } else {
                // Handle users without herbarium (new OpenID users)
                // Redirect to a page where they can select/request a herbarium
                if (!$this->presenter->isLinkCurrent(':Admin:Herbarium:request')) {
                    $this->redirect(':Admin:Herbarium:request');
                }
            }
        }

        parent::startup();
    }



}
