<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\TempDir;
use League\Pipeline\StageInterface;

abstract class BaseStage implements StageInterface
{

    protected const string ARCHIVE_MASTER = 'archive';
    protected const string THUMB_FOR_DATABOT = 'databot';
    protected const string JP2_FOR_IIIF = 'iiif';
    protected const string ZBAR = 'zbar';
    protected const string DUPLICATE = 'duplicate';
    protected Photos $item;

    public function __construct(protected readonly TempDir $tempDir, protected readonly RepositoryConfiguration $repositoryConfiguration, protected readonly ImagickService $imagickService)
    {
    }

    protected function getDatabotThumbTempPath(): string
    {
        return $this->tempDir->getPath(self::THUMB_FOR_DATABOT . '_' . $this->item->getId() . '.png');
    }

    protected function getZbarThumbTempPath(): string
    {
        return $this->tempDir->getPath(self::ZBAR . '_' . $this->item->getId() . '.png');
    }

    protected function getIiifTempPath(): string
    {
        return $this->tempDir->getPath(self::JP2_FOR_IIIF . '_' . $this->item->getId() . 'jp2');
    }

    protected function getMasterTempPath(): string
    {
        return $this->tempDir->getPath(self::ARCHIVE_MASTER . '_' . $this->item->getId() . '.' . pathinfo($this->item->getOriginalFilename(), PATHINFO_EXTENSION));
    }

    protected function getDuplicateTempPath(Photos $photo): string
    {
        return $this->tempDir->getPath(self::DUPLICATE . '_' . $photo->getId() . '.' . pathinfo($photo->getOriginalFilename(), PATHINFO_EXTENSION));
    }

}
