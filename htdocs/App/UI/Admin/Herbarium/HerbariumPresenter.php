<?php declare(strict_types=1);

namespace App\UI\Admin\Herbarium;

use App\Model\Database\Entity\Herbaria;
use App\Security\Identity;
use App\Services\EntityServices\HerbariumService;
use App\UI\Base\SecuredPresenter;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\UI\Form;

final class HerbariumPresenter extends SecuredPresenter
{

    /** @inject */
    public HerbariumService $herbariumService;

    /** @inject */
    public EntityManagerInterface $entityManager;

    public function renderDefault(): void
    {
        $this->template->title = 'Herbarium overview';
        $this->template->herbarium = $this->herbariumService->getCurrentUserHerbarium($this->user);
        $this->template->availableHerbaria = $this->getAvailableHerbaria();
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

    public function handleSwitchHerbarium(int $herbariumId): void
    {
        // Get the herbarium entity
        $herbarium = $this->entityManager->getRepository(Herbaria::class)->find($herbariumId);

        if (!$herbarium) {
            $this->flashMessage('Herbarium not found.', 'danger');
            $this->redirect('this');
        }

        // Check if user has access to this herbarium
        $identity = $this->user->getIdentity();
        if ($identity instanceof Identity) {
            try {
                $identity->switchHerbarium($herbarium);
                $this->flashMessage('Switched to herbarium ' . $herbarium->getAcronym(), 'success');
            } catch (\InvalidArgumentException $e) {
                $this->flashMessage('You do not have access to this herbarium.', 'danger');
            }
        } else {
            $this->flashMessage('Unable to switch herbarium.', 'danger');
        }

        $this->redirect('this');
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
