<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\MetadataStageException;
use League\Pipeline\StageInterface;

class MetadataStage extends BaseStage implements StageInterface
{
    protected function readDimensions(\Imagick $imagick): \Imagick
    {
        $this->item->setWidth($imagick->getImageWidth());
        $this->item->setHeight($imagick->getImageHeight());

        return $imagick;
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        try {
            $imagick = $this->imagickService->createImagick($this->getMasterTempPath());
            $this->readDimensions($imagick);
            $this->item->setIdentify($this->imagickService->readIdentify($imagick));
            $this->item->setExif($this->imagickService->readExif($imagick));
            $imagick->clear();
            unset($imagick);

            return $this->item;
        } catch (\Throwable $e) {
            throw new MetadataStageException('problem with metadata detection: '.$e->getMessage());
        }
    }
}
