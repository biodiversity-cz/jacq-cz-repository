<?php declare(strict_types=1);

namespace App\UI\Admin\Herbarium;

use App\Services\EntityServices\HerbariumService;
use App\UI\Base\SecuredPresenter;

final class HerbariumPresenter extends SecuredPresenter
{

    /** @inject */
    public HerbariumService $herbariumService;

    public function renderDefault(): void
    {
        $this->template->title = 'Herbarium overview';
        $this->template->herbarium = $this->herbariumService->find($this->user->getIdentity()->herbarium);
    }

    public function actionSetSettings(string $feature, ?string $value): void
    {
        if ($value === 'false'){
            $value = false;
        }
        if ($value === 'true'){
            $value = true;
        }
        if ($this->user->isInRole('curator')){
        match ($feature) {
            'filenameFallbackSwitch' => $this->herbariumService->setFilenameFallback($this->user, (bool)$value),
            'multiplierSwitch' => $this->herbariumService->setMultiplier($this->user, (bool)$value),
        };
        }

        $this->redirect(':default');
    }

}
