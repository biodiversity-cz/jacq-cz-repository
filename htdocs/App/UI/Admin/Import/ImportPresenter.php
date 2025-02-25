<?php declare(strict_types = 1);

namespace App\UI\Admin\Import;

use App\Controls\Image\DetailControlFactory;
use App\Exceptions\SpecimenIdException;
use App\Facades\CuratorFacade;
use App\Forms\FormFactory;
use App\Forms\ImportFormFactory;
use App\Model\Database\Entity\Photos;
use App\Model\Specimen\SpecimenFactory;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\UI\Base\SecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Form;
use Nette\Application\UI\Multiplier;
use Nette\Http\IRequest;
use Nette\Http\Response;
use Nette\Security\User;

final class ImportPresenter extends SecuredPresenter
{

    /** @inject */
    public CuratorFacade $curatorFacade;

    /** @inject */
    public PhotoService $photoService;


    /** @inject */ public FormFactory $formFactory;

    /** @inject */ public RepositoryConfiguration $repositoryConfiguration;

    /** @inject */ public S3Service $s3Service;

    /** @inject */ public SpecimenFactory $specimenFactory;

    /** @inject */ public DetailControlFactory $detailControlFactory;

    /** @inject */ public ImportFormFactory $importFormFactory;

    public ?Photos $photo;

    public function renderDefault(): void
    {
        $this->template->title = 'New Files';
        $files = $this->curatorFacade->getAvailableCuratorBucketFiles($this->user);
        $this->template->files = $files;
        $this->template->pendingPhotos = $this->photoService->pendingPhotosCount();
        $this->template->totalPendingPhotos = array_sum(array_column($this->photoService->pendingPhotosCount(), 'count'));

        $this->template->orphanedItems = $this->curatorFacade->getOrphanedItems($this->user);
        $this->template->eligible = count(array_filter($files, fn ($item) => $item->isEligibleToBeImported() === true));
        $this->template->erroneous = count(array_filter($files, fn ($item) => $item->hasControlError() === true));
        $this->template->waiting = count(array_filter($files, fn ($item) => $item->isAlreadyWaiting() === true));
        $this->template->preliminaryError = count(array_filter($files, fn ($item) => $item->isSizeOK() === false || $item->isTypeOK() === false));
    }

    public function actionThumbnail(int $id): void
    {
        $thumb = $this->photoService->getPhotoWithError($this->user, $id)?->getError()->getThumbnail();
        if ($thumb !== null) {
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
        if ($photo === null) {
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
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect('default');
    }

    public function actionReimport(int $id): void
    {
        try {
            $photo = $this->photoService->getPhotoWithError($this->user, $id);
            if ($photo === null) {
                $this->error('Photo not found');
            }

            $this->curatorFacade->reimportPhoto($this->user, $photo);
            $this->flashMessage('File successfully marked to be re-processed', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect('default');
    }

    public function actionDelete(int $id): void
    {
        try {
            $photo = $this->photoService->getPhotoWithError($this->user, $id);
            if ($photo === null) {
                $this->error('Photo not found');
            }

            $name = $photo->getOriginalFilename();
            $this->curatorFacade->deletePhoto($this->user, $photo);
            $this->flashMessage('Photo ' . $name . ' deleted.', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect(':default');
    }

    public function actionDeleteJustFile(string $id): void
    {
        try {
            $this->curatorFacade->deleteJustNotimportedFile($this->user, $id);
            $this->flashMessage('File ' . $id . ' deleted.', 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect(':default');
    }

    public function specimenIdFormSucceeded(Form $form, array $values): void
    {
        try {
            $photo = $this->photoService->getPhotoWithError($this->user, (int) $values['photoId']);
            if ($photo === null) {
                $this->error('Photo not found');
            }

            $this->curatorFacade->reimportPhoto($this->user, $this->photoService->getPhotoReference((int) $values['photoId']), (string) $values['specimen']);

            $fullID = $this->herbarium->getAcronym() . '-' . $values['specimen'];
            $this->flashMessage('File successfully marked to be re-processed with ID ' . $fullID, 'success');
        } catch (\Throwable $exception) {
            $this->flashMessage('An error occurred: ' . $exception->getMessage(), 'danger');
        }

        $this->redirect(':default');
    }

    public function renderSpecimen(?int $specimenNumericPartOfId): void
    {
        try {
            if ($specimenNumericPartOfId === null) {
                throw new SpecimenIdException();
            }

            $specimen = $this->specimenFactory->createFromNumeric($this->user, $specimenNumericPartOfId);
            $images = $this->photoService->getAllPhotosOfSpecimen($this->user, $specimen);
            if (count($images) === 0) {
                throw new SpecimenIdException('Specimen not in evidence');
            }
        } catch (SpecimenIdException $exception) {
            $this->flashMessage($exception->getMessage(), 'warning');
            $this->redirect('Home:');
        }

        $this->template->specimen = $specimen;
        $this->template->images = $this->photoService->getAllPhotosOfSpecimen($this->user, $specimen);

        $this->template->manifestAbsoluteLink = $this->link('//:Front:Iiif:manifest', $specimen->getStandardizedId());
    }

    public function renderArchiveImage(int $id): void
    {
        $photo = $this->photoService->getPhoto($this->user, $id);
        if ($photo === null) {
            $this->error('The requested photo does not exists.');
        }
//TODO user security police and only public images..
        $bucket = $this->repositoryConfiguration->getRepositoryArchiveBucket();
        $filename = $photo->getArchiveFilename();
        if ($this->s3Service->objectExists($bucket, $filename)) {
            $head = $this->s3Service->headObject($bucket, $filename);
            $stream = $this->s3Service->getStreamOfObject($bucket, $filename);

            $callback = function (IRequest $httpRequest, Response $httpResponse) use ($filename, $head, $stream): void {
                $httpResponse->setHeader('Content-Type', $head['ContentType']);
                $httpResponse->setHeader('Content-Disposition', 'inline; filename' . $filename);
                fpassthru($stream);
                fclose($stream);
            };

            $response = new CallbackResponse($callback);
            $this->sendResponse($response);
        } else {
            $this->error('The requested image does not exists.');
        }
    }

    protected function createComponentSpecimenIdForm(): Form
    {
        $form = $this->formFactory->forBackend();
        $form->addInteger('specimen', 'ID:')
            ->setRequired('Please insert only number.')
            ->addRule($form::Integer, 'It must be integer');
        $form->addHidden('photoId', $this->photo->getId());
        $form->addSubmit('submit', 'Import with this ID');
        $form->onSuccess[] = [$this, 'specimenIdFormSucceeded'];

        return $form;
    }

    protected function createComponentDetail(): Multiplier
    {
        return new Multiplier(fn ($id) => $this->detailControlFactory->create((int) $id));
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
