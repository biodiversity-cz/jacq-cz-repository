<?php declare(strict_types = 1);

namespace App\UI\Admin\Home;

use App\Facades\CuratorFacade;
use App\Grids\ImportedPhotosGrid;
use App\Grids\ImportedPhotosGridFactory;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Repository\PhotosRepository;
use App\Services\EntityServices\PhotoService;
use App\UI\Base\SecuredPresenter;

final class HomePresenter extends SecuredPresenter
{

    /** @inject */
    public CuratorFacade $curatorService;

    /** @inject */ public ImportedPhotosGridFactory $importedPhotosGridFactory;
    /** @inject */ public PhotoService $photosRepository;

    public ?Photos $photo;

    public function renderDefault(): void
    {
        $this->template->title = 'Admin';
    }

    public function renderOverview(): void
    {
        $this->template->title = 'Files before publishing';
        $this->template->publishableCount = $this->photosRepository->getPublishablePhotosDatasource()->select('count(p.id)')->getQuery()->getSingleScalarResult();
    }

    public function createComponentImportedGrid(): ImportedPhotosGrid
    {
        return $this->importedPhotosGridFactory->create();
    }

}
