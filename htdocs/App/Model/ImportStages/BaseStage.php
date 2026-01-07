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
    protected const string ARCHIVE_MASTER_SINGLEPAGE = 'archive-singlepage'; /* tif file where only the largest page is extracted */
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
        return $this->tempDir->getPath(self::THUMB_FOR_DATABOT . '.png');
    }

    protected function getZbarThumbTempPath(): string
    {
        return $this->tempDir->getPath(self::ZBAR .  '.png');
    }

    protected function getIiifTempPath(): string
    {
        return $this->tempDir->getPath(self::JP2_FOR_IIIF .  '.jp2');
    }

    protected function getMasterTempPath(): string
    {
        return $this->tempDir->getPath(self::ARCHIVE_MASTER . '.' . $this->getOriginalFileExtension($this->item));
    }

    protected function getMasterSinglePageTempPath(): string
    {
        return $this->tempDir->getPath(self::ARCHIVE_MASTER_SINGLEPAGE . '.tiff');
    }

    protected function getDuplicateTempPath(Photos $photo): string
    {
        return $this->tempDir->getPath(self::DUPLICATE . '.' . $this->getOriginalFileExtension($photo));
    }

    protected function getOriginalFileExtension(Photos $photo): string
    {
        return pathinfo($photo->originalFilename, PATHINFO_EXTENSION);
    }
}
