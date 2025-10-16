<?php declare(strict_types=1);

namespace App\UI\Admin\Funding;

use App\Forms\FundingForm;
use App\Model\Database\Entity\Funding;
use App\Services\EntityServices\FundingService;
use App\UI\Base\SecuredPresenter;

final class FundingPresenter extends SecuredPresenter
{
    /** @inject */
    public FundingService $fundingService;

    /** @inject */
    public FundingForm $fundingForm;

    protected ?Funding $funding = null;

    public function renderDefault(): void
    {
        $this->template->title = 'Funding Affiliation Management';
        $this->template->fundings = $this->fundingService->getRepository()->findAllAvailable($this->user)->getQuery()->getResult();
    }

    public function actionAdd(): void
    {
        $this->template->setFile(__DIR__ . '/edit.latte');
        $this->template->title = 'Create Funding';
        $form = $this->getComponent('fundingForm');
        $form->onSuccess[] = [$this, 'addingFormSucceeded'];
    }

    public function actionEdit(int $id): void
    {
        $this->template->title = 'Edit Funding';
        $this->funding = $this->fundingService->getRepository()->findEditable($id, $this->user)->getQuery()->getOneOrNullResult();
        if (!$this->funding) {
            $this->error();
        }
    }

    public function handleDelete(int $id): void
    {
        $funding = $this->fundingService->getRepository()->findEditable($id, $this->user)->getQuery()->getOneOrNullResult();
        if (!$funding) {
            $this->flashMessage('Funding not found.', 'danger');
            $this->redirect('default');
        }

        try {
            $this->fundingService->delete($this->user, $funding);
            $this->flashMessage('Funding deleted successfully.', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect('default');
    }

    protected function createComponentFundingForm()
    {
        if (!in_array($this->getAction(), ['add', 'edit'])) {
            $this->error();
        }
        return $this->fundingForm->create($this->funding);
    }
}
