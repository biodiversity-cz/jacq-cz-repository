<?php declare(strict_types = 1);

namespace App\UI\Admin\Home;

use App\Facades\CuratorFacade;
use App\Grids\ImportedPhotosGrid;
use App\Model\Database\Entity\Photos;
use App\UI\Base\SecuredPresenter;

final class HomePresenter extends SecuredPresenter
{

    /** @inject */
    public CuratorFacade $curatorService;
    /** @inject */ public ImportedPhotosGrid  $importedPhotosGrid;

    public ?Photos $photo;

    public function renderDefault(): void
    {
        $this->template->title = 'Admin';
        $this->template->statuses = $this->curatorService->getAllStatuses();
    }

    public function renderOverview(): void
    {
        $files = $this->curatorService->getLatestImports();
        $this->template->files = $files;
    }

    public function createComponentImportedGrid($name)
    {
        return $this->importedPhotosGrid->create($this->getPresenter(), $name);
    }

}
