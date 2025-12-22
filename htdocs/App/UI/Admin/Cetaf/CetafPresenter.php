<?php declare(strict_types=1);

namespace App\UI\Admin\Cetaf;

use App\Forms\BaseForm;
use App\Forms\ImportCetafForm;
use App\Grids\CetafSidGrid;
use App\Model\Database\Entity\ExternalDatabase;
use App\Services\CetafSidManagementService;
use App\Services\EntityServices\CetaSidService;
use App\UI\Base\SecuredPresenter;

final class CetafPresenter extends SecuredPresenter
{

    /** @inject */
    public CetaSidService $cetafSidRepository;

    /** @inject */
    public CetafSidManagementService $sidManagementService;

    /** @inject */
    public ImportCetafForm $validationForm;

    /** @inject */ public CetafSidGrid $cetafSidGrid;

    public function checkRequirements(\ReflectionClass|\ReflectionMethod $element): void
    {
        parent::startup();
        if ($this->herbarium->externalDatabase->id !== ExternalDatabase::INTERNAL) {
            $this->redirect("Homepage");
        }
    }

    public function renderErrors(): void
    {
        if ($this->sidManagementService->getErrors() === null) {
            $this->redirect(':default');
        }
        $this->template->errors = $this->sidManagementService->getErrors();
    }

    public function createComponentImportForm(): BaseForm
    {
        return $this->validationForm->create(ImportCetafForm::IMPORT);
    }

    public function createComponentValidationForm(): BaseForm
    {
        return $this->validationForm->create();
    }

    public function createComponentPublishedGrid(): CetafSidGrid
    {
        return $this->cetafSidGrid->create();
    }

}
