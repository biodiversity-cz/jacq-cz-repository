<?php declare(strict_types=1);

namespace App\Api\PubV1;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Exception\Api\ClientErrorException;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Model\Database\Entity\Photos;
use App\Model\IIIF\AnnotationListFactory;
use App\Model\IIIF\ManifestFactory;
use App\Model\Specimen\SpecimenFactory;
use App\Services\EntityServices\ImageDownloadLogService;
use App\Services\EntityServices\PhotoService;
use App\Services\RepositoryConfiguration;
use App\Services\S3Service;
use Nette\Http\IRequest;
use Psr\Http\Message\ResponseInterface;


#[Apitte\Path('/images')]
#[Apitte\Tag('Images')]
class ImageController extends BasePubV1Controller
{


    public function __construct(protected readonly SpecimenFactory $specimenFactory, protected readonly ManifestFactory $manifestFactory, protected readonly AnnotationListFactory $annotationListFactory, protected readonly PhotoService $photoService, protected readonly ImageDownloadLogService $imageDownloadLogService, protected IRequest $httpRequest, protected RepositoryConfiguration $repositoryConfiguration, protected S3Service $s3Service)
    {
    }

    #[Apitte\OpenApi('summary: Download Archive Master image')]
    #[Apitte\Path('/archive/{photoId}')]
    #[Apitte\Method('GET')]
    public function archive(ApiRequest $request, ApiResponse $response): ResponseInterface
    {
        $id = (int)  $request->getParameter('photoId');

        $photo = $this->photoService->getPublicPhoto($id);
        if ($photo === null) {
            throw new ClientErrorException('Image not found', 404);
        }

        return $this->sendFile(
            $request,
            $response,
            $photo,
            'archive'
        );

    }

    #[Apitte\OpenApi('summary: Download JP2 fullsize image')]
    #[Apitte\Path('/jp2/{photoId}')]
    #[Apitte\Method('GET')]
    public function jp2(ApiRequest $request, ApiResponse $response): ResponseInterface
    {
        $id = (int) $request->getParameter('photoId');

        $photo = $this->photoService->getPublicPhoto($id);
        if ($photo === null) {
            throw new ClientErrorException('Image not found', 404);
        }

        return $this->sendFile(
            $request,
            $response,
            $photo,
            'jp2'
        );

    }
    #[Apitte\OpenApi('summary: Download thumbnail for image')]
    #[Apitte\Path('/thumb/{photoId}')]
    #[Apitte\Method('GET')]
    public function thumb(ApiRequest $request, ApiResponse $response): ResponseInterface
    {
        $id = (int) $request->getParameter('photoId');

        $photo = $this->photoService->getPublicPhoto($id);
        if ($photo === null) {
            throw new ClientErrorException('Image not found', 404);
        }

        return $this->sendFile(
            $request,
            $response,
            $photo,
            'databot_thumb'
        );

    }

    protected function sendFile(ApiRequest $request, ApiResponse $response, Photos $photo, string $imageType): ResponseInterface
    {
        $result = match ($imageType) {
            'archive' => [
                'bucket' => $this->repositoryConfiguration->getArchiveBucket($photo),
                'filename' => $photo->archiveFilename,
            ],
            'jp2' => [
                'bucket' => $this->repositoryConfiguration->getImageServerBucket($photo),
                'filename' => $photo->jp2Filename,
            ],
            'databot_thumb' => [
                'bucket' => $this->repositoryConfiguration->getDatabotThumbsBucket($photo),
                'filename' => $photo->databotThumbFilename,
            ],
        };

        // Log the download request
        $this->imageDownloadLogService->logDownload(
            $photo->id,
            $imageType,
            $this->httpRequest->getRemoteAddress(),
            $request->getHeader('User-Agent'),
            $request->getHeader('Referer')
        );


        $head = $this->s3Service->headObject($result['bucket'], $result['filename']);
        $stream = $this->s3Service->getPsrStreamOfObject($result['bucket'], $result['filename']);

        return $response
            ->withHeader('Content-Type', $head['ContentType'])
            ->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Transfer-Encoding', 'binary')
            ->withHeader(
                'Content-Disposition',
                ('attachment')
                . '; filename="' . basename($result['filename']) . '"'
                . '; filename*=utf-8\'\'' . rawurlencode($result['filename'])
            )
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->withHeader('Pragma', 'public')
            ->withHeader('Content-Length', $stream->getSize())
            ->withBody($stream);
    }

}
