<?php

declare(strict_types=1);

namespace App\Api\PubV1;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Services\AppConfiguration;
use Psr\Http\Message\ResponseInterface;

#[Apitte\Path('/specimens')]
#[Apitte\Tag('Specimens')]
class SpecimensController extends BasePubV1Controller
{
    public function __construct(protected readonly AppConfiguration $appConfiguration)
    {
    }

    #[Apitte\OpenApi('summary: Get specimen representation')]
    #[Apitte\Path('/{herbCode}')]
    #[Apitte\Method('GET')]
    #[Apitte\Response(description: 'Success', code: '200')]
    public function get(ApiRequest $request, ApiResponse $response): ResponseInterface
    {
        $id = $request->getParameter('herbCode');

        $specimen = [
            'id' => $id,
            'exists' => true,
            'name' => 'Sample specimen',
        ];

        return $response->writeJsonBody($specimen);
    }

    #[Apitte\OpenApi('summary: Get published images of specimen')]
    #[Apitte\Path('/{herbCode}/images')]
    #[Apitte\Method('GET')]
    #[Apitte\Response(description: 'Success', code: '200')]
    public function images(ApiRequest $request, ApiResponse $response): ResponseInterface
    {
        $id = $request->getParameter('herbCode');

        // TODO: load images
        $images = [
            [
                'id' => 1,
                'url' => '/media/specimen/'.$id.'/1.jpg',
            ],
            [
                'id' => 2,
                'url' => '/media/specimen/'.$id.'/2.jpg',
            ],
        ];

        return $response->writeJsonBody([
            'specimenId' => $id,
            'images' => $images,
        ]);
    }
}
