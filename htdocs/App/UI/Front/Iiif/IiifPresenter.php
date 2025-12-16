<?php declare(strict_types = 1);

namespace App\UI\Front\Iiif;

use App\Exceptions\SpecimenIdException;
use App\Model\IIIF\ManifestFactory;
use App\Model\Specimen\Specimen;
use App\Model\Specimen\SpecimenFactory;
use App\Services\EntityServices\ImageDownloadLogService;
use App\Services\EntityServices\PhotoService;
use App\UI\Base\UnsecuredPresenter;

final class IiifPresenter extends UnsecuredPresenter
{

    /** @inject */ public SpecimenFactory $specimenFactory;

    /** @inject */ public ManifestFactory $manifestFactory;

    /** @inject */ public PhotoService $photoService;

    /** @inject */ public ImageDownloadLogService $imageDownloadLogService;

    public function actionManifest(string $id): void
    {
        $specimen = $this->getSpecimen($id);
        $manifest = $this->manifestFactory->createManifest($specimen, $this->link('//this'));

        // Log the download request
        $this->imageDownloadLogService->logDownload(
            $this->photoService->getPublicPhotosOfSpecimen($specimen)[0]->id,
            'iiif_manifest',
            $this->getHttpRequest()->getRemoteAddress(),
            $this->getHttpRequest()->getHeader('User-Agent'),
            $this->getHttpRequest()->getHeader('Referer')
        );

        $this->sendJson($manifest->toArray());
    }

    protected function getSpecimen(string $specimenFullId): Specimen
    {
        try {
            $specimen = $this->specimenFactory->create($specimenFullId);
        } catch (SpecimenIdException $exception) {
            $this->flashMessage($exception->getMessage(), 'error');
            $this->redirect('Home:');
        }

        if (!$this->photoService->specimenHasPublicPhotos($specimen)) {
            $this->error('Specimen has no public images', 404);
        }

        return $specimen;
    }

}
