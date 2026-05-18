<?php

declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\ThumbnailStageException;
use League\Pipeline\StageInterface;

class ThumbnailStage extends BaseStage implements StageInterface
{
    /**
     * thumbnail stored in db used only during control error phase to provide visualization to curators
     * we used to make it larger than the databot thumb, but this led to users' misunderstanding of images quality compared
     * using databotImageSize for now.
     */
    protected function createThumbnail(\Imagick $imagick): void
    {
        $imagick = $this->imagickService->resizeImage($imagick, $this->repositoryConfiguration->getDatabotImageSize());
        $imagick->setImageFormat('jpg');
        $imagick->setImageCompressionQuality($this->repositoryConfiguration->getPreviewQuality());
        $this->item->error->setThumbnail($imagick->getImagesBlob());
    }

    /**
     * thumbnail stored in S3 and used for Databots.
     */
    protected function createThumbnailDatabot(\Imagick $imagick): \Imagick
    {
        return $this->imagickService->preparePngThumb($imagick, $this->repositoryConfiguration->getDatabotImageSize());
    }

    public function __invoke(mixed $payload): mixed
    {
        try {
            $this->item = $payload;
            $imagick = $this->imagickService->createImagick($this->getMasterTempPath());
            $this->createThumbnail($imagick);
            $imagick->clear();
            unset($imagick);

            $imagick = $this->imagickService->createImagick($this->getMasterTempPath());
            $imagick = $this->createThumbnailDatabot($imagick);
            $imagick->writeImage($this->getDatabotThumbTempPath());
            $imagick->clear();
            unset($imagick);

            return $this->item;
        } catch (\Throwable $e) {
            throw new ThumbnailStageException('thumbnail error: '.$e->getMessage());
        }
    }
}
