<?php declare(strict_types=1);

namespace App\UI\Front\Barcode;

use App\Services\AppConfiguration;
use App\Services\PdfClient;
use App\UI\Base\UnsecuredPresenter;
use Contributte\Application\Response\ImageResponse;
use Nette\Application\Responses\CallbackResponse;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Picqer\Barcode\BarcodeGeneratorPNG;

final class BarcodePresenter extends UnsecuredPresenter
{
    public function __construct(protected PdfClient $client, AppConfiguration $appConfiguration)
    {
        parent::__construct($appConfiguration);
    }

    public function renderDefault(?string $title, ?string $subtitle, ?string $prefix, ?int $start, ?int $end): void
    {
        $this->template->title = $title;
        $this->template->subtitle = $subtitle;
        $this->template->queryParams = array_filter([
            'title' => $title,
            'subtitle' => $subtitle,
            'prefix' => $prefix,
            'start' => $start,
            'end' => $end,
        ], fn($v) => $v !== null);

        $values = [];
        for ($i = $start; $i <= $end; $i++) {
            $values[] = $prefix . ' ' . $i;
        }
        $this->template->values = $values;
    }

    public function renderImage(string $text)
    {
        /**
         * https://forum.nette.org/en/35144-send-image-from-presenter-to-latte
         */
        if ($text == '') {
            $image = Image::fromBlank(1, 20, ImageColor::rgb(255, 255, 255));
            $response = new ImageResponse($image);
            $this->sendResponse($response);
        }
        $generator = new BarcodeGeneratorPNG();

        $barcode = $generator->getBarcode($text, $generator::TYPE_CODE_39, 3, 50, [0, 0, 0]);

        $this->sendResponse(new CallbackResponse(function ($request, $response) use ($barcode) {
            $response->setContentType("image");
            $response->setExpiration('1 day');
            echo $barcode;
        }));
    }

    public function actionPdf(?string $title, ?string $subtitle, ?string $prefix, ?int $start, ?int $end): void
    {
        $queryParams = array_filter([
            'title' => $title,
            'subtitle' => $subtitle,
            'prefix' => $prefix,
            'start' => $start,
            'end' => $end,
        ], fn($v) => $v !== null);

        $query = http_build_query($queryParams);

        $url = $this->appConfiguration->getPdfBarcodeUrl()."/?" . $query;

        $pdf = $this->client->generatePdf($url, [
                'scale' => 0.43,
                'margin' => ['top' => '5mm', 'bottom' => '5mm']
            ]);

        $this->sendResponse(new CallbackResponse(function ($request, $response) use ($pdf) {
            $response->setContentType("application/pdf");
            echo $pdf;
        }));
    }
}
