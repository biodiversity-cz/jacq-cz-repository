<?php

declare(strict_types=1);

namespace App\UI\Admin\Herbarium;

use App\Security\Identity;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\UserService;
use App\Services\Exceptions\RiskOfUnpredictabilityException;
use App\UI\Base\SecuredPresenter;

final class HerbariumPresenter extends SecuredPresenter
{
    /** @inject */
    public HerbariumService $herbariumService;

    /** @inject */
    public UserService $userService;

    public function renderDefault(): void
    {
        $this->template->title = 'Herbarium overview';
        $this->template->herbarium = $this->herbariumService->getCurrentUserHerbarium($this->user);
        $this->template->availableHerbaria = $this->userEntity->getHerbaria();
    }

    public function actionSetSettings(string $feature, ?string $value): void
    {
        if ('false' === $value) {
            $value = false;
        }
        if ('true' === $value) {
            $value = true;
        }
        try {
            if ($this->user->isInRole('curator')) {
                match ($feature) {
                    'filenameFallbackSwitch' => $this->herbariumService->setFilenameFallback($this->user, (bool) $value),
                    'multiplierSwitch' => $this->herbariumService->setMultiplier($this->user, (bool) $value),
                    'strictAcronymPrefix' => $this->herbariumService->setStrictAcronymPrefix($this->user, (bool) $value),
                };
            }
        } catch (RiskOfUnpredictabilityException $exception) {
            $this->flashMessage($exception->getMessage(), 'warning');
        }

        $this->redirect(':default');
    }

    public function handleSwitchHerbarium(int $herbariumId): void
    {
        // Get the herbarium entity
        $herbarium = $this->herbariumService->find($herbariumId);
        if (!$herbarium || !$this->user->getIdentity()->isEligibleForHerbarium($herbarium)) {
            $this->flashMessage('Herbarium not found.', 'danger');
            $this->redirect('this');
        }
        $this->userService->changeActiveHerbarium($this->userEntity, $herbarium);
        $this->user->logout();
        $this->redirect(':Front:Sign:in');
    }

    private function getAvailableHerbaria(): array
    {
        $identity = $this->user->getIdentity();
        if ($identity instanceof Identity) {
            return $identity->getAvailableHerbaria();
        }

        // Fallback to the old method
        return [];
    }
}
