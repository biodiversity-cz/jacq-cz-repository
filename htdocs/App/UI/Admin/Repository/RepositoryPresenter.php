<?php declare(strict_types = 1);

namespace App\UI\Admin\Repository;

use App\Controls\Image\DetailControlFactory;
use App\Controls\Specimen\SpecimenControl;
use App\Controls\Specimen\SpecimenControlFactory;
use App\Exceptions\SpecimenIdException;
use App\Facades\CuratorFacade;
use App\Grids\Admin\ImportedPhotosGrid;
use App\Grids\Admin\ImportedPhotosGridFactory;
use App\Grids\Admin\PublishedPhotosGrid;
use App\Grids\Admin\PublishedPhotosGridFactory;
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

    /** @inject */ public CuratorFacade $curatorFacade;
    /** @inject */ public ImportedPhotosGridFactory $importedPhotosGridFactory;
    /** @inject */ public PublishedPhotosGridFactory $publishedPhotosGrid;
    /** @inject */ public SpecimenControlFactory $specimenControlFactory;
    /** @inject */ public PhotoService $photoService;
    /** @inject */ public RepositoryConfiguration $repositoryConfiguration;
    /** @inject */ public S3Service $s3Service;
    /** @inject */ public SpecimenFactory $specimenFactory;
    /** @inject */ public DetailControlFactory $detailControlFactory;

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

    /**
     * used for GET form from main menu to make nice URL
     */
    public function actionSearch(?string $numeric_part): void
    {
        $this->redirect('specimen', ['id'=>$numeric_part]);
    }

    public function renderSpecimen(?string $id = ''): void
    {
        try {
            if ($id == null) {
                throw new SpecimenIdException('Unknown specimen');
            }

            $specimen = $this->specimenFactory->createFromNumeric($this->user, (int) $id);
            $images = $this->photoService->getAllPhotosOfSpecimen($this->user, $specimen);
            if (count($images) === 0) {
                throw new SpecimenIdException('Specimen not in evidence');
            }
        } catch (SpecimenIdException $exception) {
            $this->flashMessage($exception->getMessage(), 'warning');
            $this->redirect('Home:');
        }

        $this->template->images = $images;

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

    public function createComponentSpecimen(): SpecimenControl
    {
        return $this->specimenControlFactory->create();
    }

    public function createComponentPublishedGrid(): PublishedPhotosGrid
    {
        return $this->publishedPhotosGrid->create();
    }

}
