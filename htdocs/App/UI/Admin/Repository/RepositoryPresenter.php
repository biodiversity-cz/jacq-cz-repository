<?php declare(strict_types = 1);

namespace App\UI\Admin\Repository;

use App\Controls\Image\DetailControlFactory;
use App\Exceptions\SpecimenIdException;
use App\Facades\CuratorFacade;
use App\Grids\ImportedPhotosGrid;
use App\Grids\ImportedPhotosGridFactory;
use App\Grids\PublishedPhotosGrid;
use App\Grids\PublishedPhotosGridFactory;
use App\Model\Database\Entity\Photos;
use App\Model\Specimen\SpecimenFactory;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\UI\Base\SecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Multiplier;
use Nette\Http\IRequest;
use Nette\Http\Response;

final class RepositoryPresenter extends SecuredPresenter
{

    /** @inject */
    public CuratorFacade $curatorFacade;

    /** @inject */ public ImportedPhotosGridFactory $importedPhotosGridFactory;
    /** @inject */ public PublishedPhotosGridFactory $publishedPhotosGrid;
    /** @inject */ public PhotoService $photoService;
    /** @inject */
    public RepositoryConfiguration $repositoryConfiguration;

    /** @inject */
    public S3Service $s3Service;
    /** @inject */
    public SpecimenFactory $specimenFactory;

    /** @inject */
    public DetailControlFactory $detailControlFactory;

    public ?Photos $photo;

    public function actionMarkPublishable()
    {
        try{
            $this->curatorFacade->markPublishable($this->user);
            $this->flashMessage('All available photos were marked as Waiting for Publishing and will be processed soon.', 'success');
        }catch (\Exception $exception){
            $this->flashMessage('Error in publishing: '. $exception->getMessage(), 'danger');
        }

        $this->redirect("in-progress");
    }

    public function renderInProgress(): void
    {
        $this->template->title = 'Files before publishing';
        $this->template->publishableCount = $this->photoService->getPublishablePhotosDatasource($this->user)->select('count(p.id)')->getQuery()->getSingleScalarResult();
    }

    public function renderPublished(): void
    {
        $this->template->title = 'Published files';
    }
    protected function sendFile(string $bucket, string $filename)
    {
        if ($this->s3Service->objectExists($bucket, $filename)) {
            $head = $this->s3Service->headObject($bucket, $filename);
            $stream = $this->s3Service->getStreamOfObject($bucket, $filename);

            $callback = function (IRequest $httpRequest, Response $httpResponse) use ($filename, $head, $stream): void {
                $httpResponse->setHeader('Content-Type', $head['ContentType']);
                $httpResponse->setHeader(
                    'Content-Disposition',
                    "attachment; filename=\"" . basename($filename) . "\"; filename*=UTF-8''" . rawurlencode($filename)
                );
                fpassthru($stream);
                fclose($stream);
            };

            $response = new CallbackResponse($callback);
            $this->sendResponse($response);
        } else {
            $this->error('The requested image does not exists.');
        }
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

        $hasPublicImage = false;
        foreach ($this->template->images as $image) {
            if ($image->isPublic()) {
                $hasPublicImage = true;
            }
        }

        $this->template->hasPublicImage = $hasPublicImage;

        $this->template->manifestAbsoluteLink = $this->link('//:Front:Iiif:manifest', $specimen->getStandardizedId());
    }

    public function renderPhoto(int $id): void
    {
        $photo = $this->photoService->getPhoto($this->user, $id);
        if ($photo === null) {
            $this->error('The requested photo does not exists.');
        }
        $this->template->photo = $photo;
    }

    public function actionDatabotThumbImage(int $id): void
    {
        $photo = $this->photoService->getPhoto($this->user, $id);
        if ($photo === null) {
            $this->error('The requested photo does not exists.');
        }

        $this->sendFile($this->repositoryConfiguration->getDatabotThumbsBucket($photo), $photo->databotThumbFilename);

    }

    public function actionMasterImage(int $id): void
    {
        $photo = $this->photoService->getPhoto($this->user, $id);
        if ($photo === null) {
            $this->error('The requested photo does not exists.');
        }

        $this->sendFile($this->repositoryConfiguration->getArchiveBucket($photo), $photo->archiveFilename);

    }

    public function actionJP2Image(int $id): void
    {
        $photo = $this->photoService->getPhoto($this->user, $id);
        if ($photo === null) {
            $this->error('The requested photo does not exists.');
        }

        $this->sendFile($this->repositoryConfiguration->getImageServerBucket($photo), $photo->jp2Filename);

    }

    protected function createComponentDetail(): Multiplier
    {
        return new Multiplier(fn($id) => $this->detailControlFactory->create((int)$id));
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
