<?php declare(strict_types = 1);

namespace App\UI\Admin\Home;

use App\Facades\CuratorFacade;
use App\Grids\ImportedPhotosGrid;
use App\Grids\ImportedPhotosGridFactory;
use App\Model\Database\Entity\Photos;
use App\UI\Base\SecuredPresenter;

final class HomePresenter extends SecuredPresenter
{

    /** @inject */
    public CuratorFacade $curatorService;

    /** @inject */ public ImportedPhotosGridFactory $importedPhotosGridFactory;

    public ?Photos $photo;

    public function renderDefault(): void
    {
        $this->template->title = 'Admin';
    }

    public function createComponentImportedGrid(): ImportedPhotosGrid
    {
        return $this->importedPhotosGridFactory->create();
    }

}
