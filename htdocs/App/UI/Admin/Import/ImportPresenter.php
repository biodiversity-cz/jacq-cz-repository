<?php

declare(strict_types=1);

namespace App\UI\Admin\Import;

use App\Facades\CuratorFacade;
use App\Forms\FormFactory;
use App\Forms\ImportFormFactory;
use App\Model\Database\Entity\Photos;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\UI\Base\SecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Form;

final class ImportPresenter extends SecuredPresenter
{
    /** @inject */
    public CuratorFacade $curatorFacade;

    /** @inject */
    public PhotoService $photoService;

    /** @inject */
    public FormFactory $formFactory;

    /** @inject */
    public RepositoryConfiguration $repositoryConfiguration;

    /** @inject */
    public S3Service $s3Service;

    /** @inject */
    public ImportFormFactory $importFormFactory;

    public ?Photos $photo;

    public function renderDefault(): void
    {
        $this->template->title = 'New Files';
        $files = $this->curatorFacade->getAvailableCuratorBucketFiles($this->user);
        $this->template->files = $files;
        $this->template->pendingPhotos = $this->photoService->pendingPhotosCount();
        $this->template->totalPendingPhotos = array_sum(array_column($this->photoService->pendingPhotosCount(), 'count'));

        $this->template->orphanedItems = $this->curatorFacade->getOrphanedItems($this->user);
        $this->template->eligible = count(array_filter($files, fn ($item) => true === $item->isEligibleToBeImported()));
        $this->template->erroneous = count(array_filter($files, fn ($item) => true === $item->hasControlError()));
        $this->template->waiting = count(array_filter($files, fn ($item) => true === $item->isAlreadyWaiting()));
        $this->template->preliminaryError = count(array_filter($files, fn ($item) => false === $item->isSizeOK() || false === $item->isTypeOK()));
        $this->template->herbarium = $this->herbariumService->getCurrentUserHerbarium($this->user);
    }

    public function actionThumbnail(int $id): void
    {
        $thumb = $this->photoService->getPhotoWithError($this->user, $id)?->error->thumbnail;
        if (null !== $thumb) {
            $this->sendResponse(new CallbackResponse(function ($request, $response) use ($thumb): void {
                $response->setContentType('image');
                $response->setExpiration('1 hour');
                echo stream_get_contents($thumb);
            }));
        } else {
            $this->error('Thumbnail not found');
        }
    }

    public function actionRevise(int $id): void
    {
        $photo = $this->photoService->getPhotoWithError($this->user, $id);
        if (null === $photo) {
            $this->error('Photo not found');
        }

        $this->template->photo = $photo;
        $this->photo = $photo;
    }

    public function actionDeleteErroneous(): void
    {
        try {
            $erroneous = $this->photoService->getPhotosWithError($this->user);
            foreach ($erroneous as $photoWithImportError) {
                $this->curatorFacade->deletePhoto($this->user, $photoWithImportError);
            }

            $this->flashMessage('Files with import error were deleted from your herbarium bucket', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: '.$exception->getMessage(), 'danger');
        }

        $this->redirect('default');
    }

    public function actionReimport(int $id): void
    {
        try {
            $photo = $this->photoService->getPhotoWithError($this->user, $id);
            if (null === $photo) {
                $this->error('Photo not found');
            }

            $this->curatorFacade->reimportPhoto($this->user, $photo);
            $this->flashMessage('File successfully marked to be re-processed', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: '.$exception->getMessage(), 'danger');
        }

        $this->redirect('default');
    }

    public function actionDelete(int $id): void
    {
        try {
            $photo = $this->photoService->getPhotoWithError($this->user, $id);
            if (null === $photo) {
                $this->error('Photo not found');
            }

            $name = $photo->originalFilename;
            $this->curatorFacade->deletePhoto($this->user, $photo);
            $this->flashMessage('Photo '.$name.' deleted.', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: '.$exception->getMessage(), 'danger');
        }

        $this->redirect(':default');
    }

    public function actionDeleteJustFile(string $id): void
    {
        try {
            $this->curatorFacade->deleteJustNotimportedFile($this->user, $id);
            $this->flashMessage('File '.$id.' deleted.', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: '.$exception->getMessage(), 'danger');
        }

        $this->redirect(':default');
    }

    public function specimenIdFormSucceeded(Form $form, array $values): void
    {
        try {
            $photo = $this->photoService->getPhotoWithError($this->user, (int) $values['photoId']);
            if (null === $photo) {
                $this->error('Photo not found');
            }

            $this->curatorFacade->reimportPhoto($this->user, $this->photoService->getPhotoReference((int) $values['photoId']), $values['specimen']);

            $fullID = $this->herbarium->acronym.'-'.$values['specimen'];
            $this->flashMessage('File successfully marked to be re-processed with ID '.$fullID, 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: '.$exception->getMessage(), 'danger');
        }

        $this->redirect(':default');
    }

    protected function createComponentSpecimenIdForm(): Form
    {
        $form = $this->formFactory->forBackend();
        $form->addText('specimen', 'ID:')
            ->setRequired('Please insert only number.');
        $form->addHidden('photoId', $this->photo->id);
        $form->addSubmit('submit', 'Import with this ID');
        $form->onSuccess[] = [$this, 'specimenIdFormSucceeded'];

        return $form;
    }

    protected function createComponentImportForm(): Form
    {
        $form = $this->importFormFactory->create();
        $form->onSuccess[] = function (): void {
            $this->flashMessage('Photos marked for processing', 'success');
            $this->redirect('default');
        };

        return $form;
    }
}
