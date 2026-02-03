<?php declare(strict_types=1);

namespace App\UI\Front\Repository;

use App\Controls\Image\DetailControlFactory;
use App\Exceptions\HerbariumIdException;
use App\Exceptions\ImageIdException;
use App\Exceptions\SpecimenIdException;
use App\Grids\FrontPhotosGrid;
use App\Grids\FrontPhotosGridFactory;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\Specimen\SpecimenFactory;
use App\Services\EntityServices\HerbariumService;
use App\Services\EntityServices\ImageDownloadLogService;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use App\UI\Base\UnsecuredPresenter;
use Nette\Application\Responses\CallbackResponse;
use Nette\Application\UI\Multiplier;
use Nette\Http\IRequest;
use Nette\Http\Response;

final class RepositoryPresenter extends UnsecuredPresenter
{

    /** @inject */
    public S3Service $s3Service;

    /** @inject */
    public SpecimenFactory $specimenFactory;

    /** @inject */
    public PhotoService $photoService;

    /** @inject */
    public HerbariumService $herbariumService;

    /** @inject */
    public FrontPhotosGridFactory $frontPhotosGridFactory;

    /** @inject */
    public RepositoryConfiguration $repositoryConfiguration;

    /** @inject */
    public DetailControlFactory $detailControlFactory;

    protected(set) ?Herbaria $herbarium = null;

    /** @inject */ public ImageDownloadLogService $imageDownloadLogService;

    public function actionArchiveImage(int $id): void
    {
        $photo = $this->photoService->getPublicPhoto($id);
        if ($photo === null) {
            $this->error('The requested photo does not exists.');
        }

        $this->sendFile($this->repositoryConfiguration->getArchiveBucket($photo), $photo->archiveFilename, $id, 'archive');
    }

    public function actionJp2Image(int $id): void
    {
        $photo = $this->photoService->getPublicPhoto($id);
        if ($photo === null) {
            $this->error('The requested photo does not exists.');
        }

        $this->sendFile($this->repositoryConfiguration->getImageServerBucket($photo), $photo->jp2Filename, $id, 'jp2');
    }

    public function actionDatabotThumbImage(int $id): void
    {
        $photo = $this->photoService->getPublicPhoto($id);
        if ($photo === null) {
            $this->error('The requested photo does not exists.');
        }

        $this->sendFile($this->repositoryConfiguration->getDatabotThumbsBucket($photo), $photo->databotThumbFilename, $id, 'databot_thumb');
    }

    protected function sendFile(string $bucket, string $filename, int $photoId, string $imageType)
    {
        // Log the download request
        $this->imageDownloadLogService->logDownload(
            $photoId,
            $imageType,
            $this->getHttpRequest()->getRemoteAddress(),
            $this->getHttpRequest()->getHeader('User-Agent'),
            $this->getHttpRequest()->getHeader('Referer')
        );

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

    public function renderSpecimen(?string $sid): void
    {
        try {
            if ($sid === null) {
                throw new SpecimenIdException();
            }

            $specimen = $this->specimenFactory->create($sid);
        } catch (SpecimenIdException $exception) {
            $this->flashMessage($exception->getMessage(), 'error');
            $this->redirect('Home:');
        }

        if (!$this->photoService->specimenHasPublicPhotos($specimen)) {
            $this->error('Specimen ' . $sid . ' not in evidence.');
        }

        $this->template->specimen = $specimen;
        $this->template->images = $this->photoService->getPublicPhotosOfSpecimen($specimen);

        $this->template->manifestAbsoluteLink = $this->link('//Iiif:manifest', $sid);
    }

    public function renderImage(?int $id): void
    {
        try {
            if ($id === null) {
                throw new ImageIdException();
            }
            /** @var Photos $photo */
            $photo = $this->photoService->getPhoto($this->getUser(), $id);
        } catch (ImageIdException $exception) {
            $this->flashMessage($exception->getMessage(), 'error');
            $this->redirect('Home:');
        }

        $this->template->photo = $photo;

    }

    public function renderHerbarium(?string $id): void
    {
        try {
            if ($id === null) {
                throw new HerbariumIdException();
            }
            $herbarium = $this->herbariumService->findOneWithAcronym($id);
            if ($herbarium === null) {
                throw new HerbariumIdException();
            }
            $this->herbarium = $herbarium;
        } catch (HerbariumIdException $exception) {
            $this->flashMessage($exception->getMessage(), 'error');
            $this->redirect('Home:');
        }

        $this->template->herbarium = $herbarium;

    }

    protected function createComponentDetail(): Multiplier
    {
        return new Multiplier(fn($id) => $this->detailControlFactory->create((int)$id));
    }

    public function createComponentPhotosGrid(): FrontPhotosGrid
    {
        return $this->frontPhotosGridFactory->create($this->herbarium);
    }
}
