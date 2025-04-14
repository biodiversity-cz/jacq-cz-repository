<?php declare(strict_types=1);

namespace App\UI\Front\Barcode;

use App\UI\Base\UnsecuredPresenter;
use Contributte\Application\Response\ImageResponse;
use Nette\Application\Responses\CallbackResponse;
use Nette\Utils\Image;
use Nette\Utils\ImageColor;
use Picqer\Barcode\BarcodeGeneratorPNG;

final class BarcodePresenter extends UnsecuredPresenter
{

    public function renderDefault(?string $title, ?string $subtitle, ?string $prefix, ?int $start, ?int $end): void
    {
        $this->template->title = $title;
        $this->template->subtitle = $subtitle;
        $values = [];
        for ($i = $start; $i <= $end; $i++) {
            $values[] = $prefix.' '.$i;
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
}
