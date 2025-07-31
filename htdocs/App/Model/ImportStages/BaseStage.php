<?php declare(strict_types = 1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Services\TempDir;
use League\Pipeline\StageInterface;

abstract class BaseStage implements StageInterface
{
    public function __construct(protected readonly TempDir $tempDir)
    {
    }

    protected const string ARCHIVE_MASTER = 'archive';
    protected const string THUMB_FOR_DATABOT = 'databot';
    protected const string JP2_FOR_IIIF = 'iiif';

    protected function getDatabotThumbPath(Photos $photo): string
    {
        return $this->tempDir->getPath(self::THUMB_FOR_DATABOT . '_'.$photo->getId().'.' . pathinfo($photo->getOriginalFilename(), PATHINFO_EXTENSION));
    }

}
