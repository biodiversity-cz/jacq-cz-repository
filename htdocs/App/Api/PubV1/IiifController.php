<?php declare(strict_types=1);

namespace App\Api\PubV1;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Exceptions\SpecimenIdException;
use App\Model\IIIF\AnnotationListFactory;
use App\Model\IIIF\ManifestFactory;
use App\Model\Specimen\Specimen;
use App\Model\Specimen\SpecimenFactory;
use App\Services\EntityServices\ImageDownloadLogService;
use App\Services\EntityServices\PhotoService;
use Nette\Http\IRequest;
use Psr\Http\Message\ResponseInterface;


#[Apitte\Path('/iiif')]
#[Apitte\Tag('IIIF')]
class IiifController extends BasePubV1Controller
{


    public function __construct(protected readonly SpecimenFactory $specimenFactory, protected readonly ManifestFactory $manifestFactory, protected readonly AnnotationListFactory $annotationListFactory, protected readonly PhotoService $photoService, protected readonly ImageDownloadLogService $imageDownloadLogService, protected IRequest $httpRequest)
    {
    }

    #[Apitte\OpenApi('summary: IIIF v2 manifest for a specimen ID')]
    #[Apitte\Path('/manifest/{herbCode}')]
    #[Apitte\Method('GET')]
    public function manifest(ApiRequest $request, ApiResponse $response): ResponseInterface
    {
        $id = $request->getParameter('herbCode');
        try {
            $specimen = $this->getSpecimen($id);
        } catch (\Exception $e) {
            throw new ClientErrorException('Specimen not found', 404);
        }
        $manifest = $this->manifestFactory->createManifest($specimen, $this->httpRequest->getUrl()->getAbsoluteUrl());

        // Log the request
        $this->imageDownloadLogService->logDownload(
            $this->photoService->getPublicPhotosOfSpecimen($specimen)[0]->id,
            'iiif_manifest',
            $this->httpRequest->getRemoteAddress(),
            $request->getHeader('User-Agent')[0],
            $request->getHeader('Referer')[0]
        );

        return $response->writeJsonBody($manifest->toArray());

    }

    #[Apitte\OpenApi('summary: IIIF v2.1 and prior compatible Open Annotation of a photo')]
    #[Apitte\Path('/annotationList/{photoId}')]
    #[Apitte\Method('GET')]
    public function annotationList(ApiRequest $request, ApiResponse $response): ResponseInterface
    {
        $id = (int) $request->getParameter('photoId');
        $photo = $this->photoService->getPublicPhoto($id);

        $annotationList = $this->annotationListFactory->createList($photo, $this->httpRequest->getUrl()->getAbsoluteUrl());
        return $response->writeJsonBody($annotationList->toArray());

    }

//    #[Apitte\OpenApi('summary: IIIF v3 compatible W3C Web Annotation of a photo - TODO')]
//    #[Apitte\Path('/annotationPage/{photoId}')]
//    #[Apitte\Method('GET')]
//    public function annotationPage(ApiRequest $request, ApiResponse $response): ResponseInterface
//    {
//        $id = (int) $request->getParameter('photoId');
//        $photo = $this->photoService->getPublicPhoto($id);
//
//        $annotationList = $this->annotationListFactory->createList($photo, $this->httpRequest->getUrl()->getAbsoluteUrl());
//        return $response->writeJsonBody($annotationList->toArray());
//
//    }

    protected function getSpecimen(string $specimenFullId): Specimen
    {

        $specimen = $this->specimenFactory->create($specimenFullId);

        if (!$this->photoService->specimenHasPublicPhotos($specimen)) {
            throw new SpecimenIdException('Specimen has no public images', 404);
        }

        return $specimen;
    }

}
