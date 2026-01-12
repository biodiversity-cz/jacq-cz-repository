<?php declare(strict_types = 1);

namespace App\UI\Admin\Home;

use App\Facades\CuratorFacade;
use App\Grids\ImportedPhotosGrid;
use App\Grids\ImportedPhotosGridFactory;
use App\Grids\PublishedPhotosGrid;
use App\Grids\PublishedPhotosGridFactory;
use App\Model\Database\Entity\Photos;
use App\Model\Database\Repository\PhotosRepository;
use App\Services\EntityServices\PhotoService;
use App\UI\Base\SecuredPresenter;

final class HomePresenter extends SecuredPresenter
{

    /** @inject */
    public CuratorFacade $curatorFacade;

    /** @inject */ public ImportedPhotosGridFactory $importedPhotosGridFactory;
    /** @inject */ public PublishedPhotosGridFactory $publishedPhotosGrid;
    /** @inject */ public PhotoService $photosRepository;

    public ?Photos $photo;

    public function renderDefault(): void
    {
        $this->template->title = 'Admin';
    }

    public function actionMarkPublishable()
    {
        try{
            $this->curatorFacade->markPublishable($this->user);
            $this->flashMessage('All available photos were marked as Waiting for Publishing and will be processed soon.', 'success');
        }catch (\Exception $exception){
            $this->flashMessage('Error in publishing: '. $exception->getMessage(), 'danger');
        }

        $this->redirect("overview");
    }

    public function renderOverview(): void
    {
        $this->template->title = 'Files before publishing';
        $this->template->publishableCount = $this->photosRepository->getPublishablePhotosDatasource($this->user)->select('count(p.id)')->getQuery()->getSingleScalarResult();
    }

    public function renderPublished(): void
    {
        $this->template->title = 'Published files';
    }

    public function createComponentImportedGrid(): ImportedPhotosGrid
    {
        return $this->importedPhotosGridFactory->create();
    }

    public function createComponentPublishedGrid(): PublishedPhotosGrid
    {
        return $this->publishedPhotosGrid->create();
    }

}
