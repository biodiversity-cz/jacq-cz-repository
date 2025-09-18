<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\BarcodeStageException;
use App\Services\SpecimenIdService;
use Imagick;
use League\Pipeline\StageInterface;
use Throwable;

class BarcodeStage extends BaseStage implements StageInterface
{
    /** @var string [] */
    protected array $barcodes;

    public function __invoke(mixed $payload): mixed
    {
        try {
            $this->item = $payload;
            /**
             * skip detection when manually inserted id
             */
            if ($this->item->getSpecimenId() === null) {
                $imagick = $this->imagickService->createImagick($this->getMasterTempPath());
                $this->createContrastedImage($imagick);
                $this->detectCodes();
                if (empty($this->barcodes)) {
                    $this->noBarcodeDetected();
                } else {
                    $this->harvestCodes();
                }
            }

            return $this->item;
        } catch (BarcodeStageException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new BarcodeStageException('problem with barcode processing: ' . $e->getMessage());
        } finally {
            $path = $this->getZbarThumbTempPath();
            if ($path && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    protected function createContrastedImage(Imagick $imagick): void
    {
        $imagick = $this->imagickService->resizeImage($imagick, $this->repositoryConfiguration->getZbarImageSize());
        $imagick->modulateImage(100, 0, 100);
        // adaptive threshold had worse results than unmodified image        * $imagick->adaptiveThresholdImage(150, 150, 1);
        $imagick->setImageFormat('png');
        $imagick->writeImage($this->getZbarThumbTempPath());
        $imagick->clear();
        unset($imagick);
    }

    /**
     * use Zbar to detect Barcodes
     *
     * @link https://manpages.ubuntu.com/manpages/jammy/man1/zbarimg.1.html
     */
    protected function detectCodes(): void
    {
        $output = [];
        $returnVar = 0;
        $info = exec('zbarimg --quiet --raw ' . escapeshellarg($this->getZbarThumbTempPath()), $output, $returnVar);

        switch ($returnVar) {
            case 1:
            case 2:
                throw new BarcodeStageException('zbar script error: ' . $info);
            case 4:
                //no barcode detected - but let's relax, here is only detection
        }

        $this->barcodes = $output;
    }

    protected function noBarcodeDetected(): void
    {
        if (!$this->item->getHerbarium()->usesFilenameFallback()) {
            throw new BarcodeStageException('No barcode detected');
        }

        $parts = [];
        if (!preg_match($this->item->getHerbarium()->getRegexFilename(), $this->item->getOriginalFilename(), $parts)) {
            throw new BarcodeStageException('No barcode detected & invalid filename');
        }

        $this->item->setSpecimenId($parts[SpecimenIdService::REGEX_SPECIMEN]);
    }

    protected function harvestCodes(): void
    {
        $validCodes = [];
        foreach ($this->barcodes as $code) {
            $parts = [];
            if (preg_match($this->item->getHerbarium()->getRegexBarcode(), $code, $parts)) {
                if ($this->item->getHerbarium()->getAcronym() === strtoupper($parts['herbarium']) && $parts['specimenId'] !== '') {
                    $validCodes[] = $parts['specimenId'];
                }
            }
        }

        if (empty($validCodes)) {
            $this->item->getError()->setBarcodes(implode($this->barcodes));
            throw new BarcodeStageException('Invalid or missing barcode');
        }
        if (count($validCodes) === 1) {
            $this->item->setSpecimenId($validCodes[0]);
            return;
        }
        //multiple valid barcodes
        if ($this->item->getHerbarium()->hasMultipleBarcodeMultiplier()) {
            $this->item->setSpecimenId(array_shift($validCodes));
            $this->item->addMultiplier()->setBarcodes($validCodes);
        } else {
            $this->item->getError()->setBarcodes(implode($this->barcodes));
            throw new BarcodeStageException('Multiple valid barcodes detected');
        }
    }

}
