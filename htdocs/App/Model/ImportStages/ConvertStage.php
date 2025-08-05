<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\ImportStages\Exceptions\ConvertStageException;
use League\Pipeline\StageInterface;

class ConvertStage extends BaseStage implements StageInterface
{

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        try {
            $imagick = $this->imagickService->createImagick($this->getMasterTempPath());
            $imagick->setImageFormat('jp2');
            $imagick->setImageCompressionQuality($this->repositoryConfiguration->getJp2Quality());
            $imagick->writeImage($this->getIiifTempPath());
            $imagick->clear();
            unset($imagick);
            $payload->setJp2FileSize(filesize($this->getIiifTempPath()));
        } catch (\Throwable $exception) {
            throw new ConvertStageException('unable convert to JP2 (' . $exception->getMessage() . '): ' . $payload->getId());
        }

        return $payload;
    }

}
