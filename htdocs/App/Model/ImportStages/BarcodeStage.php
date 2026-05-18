<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\BarcodeStageException;
use App\Services\SpecimenIdService;
use League\Pipeline\StageInterface;

class BarcodeStage extends BaseStage implements StageInterface
{
    /** @var string [] */
    protected array $barcodes;

    public function __invoke(mixed $payload): mixed
    {
        try {
            $this->item = $payload;
            /*
             * process detection only when have not manually inserted id
             */
            if (null === $this->item->specimenId) {
                $this->createContrastedImage();
                $this->detectCodes();

                /*
                 * if no barcode detected, try again with larger image
                 */
                if (empty($this->barcodes)) {
                    $this->createContrastedImage(1.5);
                    $this->detectCodes();
                }

                if (empty($this->barcodes)) {
                    $this->noBarcodeDetected();
                } else {
                    $this->harvestCodes();
                }
            }

            return $this->item;
        } catch (BarcodeStageException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new BarcodeStageException('problem with barcode processing: '.$e->getMessage());
        } finally {
            $path = $this->getZbarThumbTempPath();
            if ($path && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    protected function createContrastedImage(float $scaleFactor = 1): void
    {
        $imagick = $this->imagickService->createImagick($this->getMasterTempPath());
        $longerSideLenght = $this->repositoryConfiguration->getZbarImageSize() * $scaleFactor;
        $imagick = $this->imagickService->resizeImage($imagick, (int) $longerSideLenght);
        $imagick->modulateImage(100, 0, 100);
        // adaptive threshold had worse results than unmodified image        * $imagick->adaptiveThresholdImage(150, 150, 1);
        $imagick->setImageFormat('png');
        $imagick->writeImage($this->getZbarThumbTempPath());
        $imagick->clear();
        unset($imagick);
    }

    /**
     * use Zbar to detect Barcodes.
     *
     * @see https://manpages.ubuntu.com/manpages/jammy/man1/zbarimg.1.html
     */
    protected function detectCodes(): void
    {
        $output = [];
        $returnVar = 0;
        $info = exec('zbarimg --quiet --raw '.escapeshellarg($this->getZbarThumbTempPath()), $output, $returnVar);

        switch ($returnVar) {
            case 1:
            case 2:
                throw new BarcodeStageException('zbar script error: '.$info);
            case 4:
                // no barcode detected - but let's relax, here is only detection
        }

        $this->barcodes = $output;
    }

    protected function noBarcodeDetected(): void
    {
        if (!$this->item->herbarium->fallbackFilename) {
            throw new BarcodeStageException('No barcode detected');
        }

        $parts = [];
        if (!preg_match($this->item->herbarium->regexFilename, $this->item->originalFilename, $parts)) {
            throw new BarcodeStageException('No barcode detected & invalid filename');
        }

        $this->item->setSpecimenId($parts[SpecimenIdService::REGEX_SPECIMEN]);
    }

    protected function harvestCodes(): void
    {
        $validCodes = [];
        foreach ($this->barcodes as $code) {
            $validCode = $this->validateBarcode($code);
            if (!empty($validCode)) {
                $validCodes[] = $validCode;
            }
        }

        if (empty($validCodes)) {
            $this->item->error->setBarcodes(implode($this->barcodes));
            throw new BarcodeStageException('Invalid or missing barcode');
        }
        if (1 === count($validCodes)) {
            $this->item->setSpecimenId($validCodes[0]);

            return;
        }
        // multiple valid barcodes
        if ($this->item->herbarium->multipleBarcodeMultiplier) {
            $this->item->setSpecimenId(array_shift($validCodes));
            $this->item->addMultiplier()->setBarcodes($validCodes);
        } else {
            $this->item->error->setBarcodes(implode($this->barcodes));
            throw new BarcodeStageException('Multiple valid barcodes detected');
        }
    }

    protected function validateBarcode($barcode): ?string
    {
        // TODO ? first and last character must be alfanumeric to prevent white char chaos?
        $parts = [];
        if (!preg_match($this->item->herbarium->regexBarcode, $barcode, $parts)) {
            return null;
        }
        $specimenId = $parts['specimenId'] ?? null;
        if (empty($specimenId)) {
            return null;
        }

        if ($this->item->herbarium->strictBarcodeAcronymPrefix
            && strtoupper($parts['herbarium'] ?? '') !== $this->item->herbarium->acronym) {
            return null;
        }

        return $specimenId;
    }
}
