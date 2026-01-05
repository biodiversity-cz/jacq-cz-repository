<?php declare(strict_types = 1);

namespace App\Model\FileManagement;

use Aws\Api\DateTimeResult;

class FileInsideCuratorBucket
{

    public const int MIN_FILESIZE = 5 * 1024 * 1024;
    public const int MAX_FILESIZE = 650 * 1024 * 1024;

    public const string EXTENSION = 'tif';
    public const string MIME_TYPE = 'image/tiff';

    protected bool $isEligibleForImport = false;

    public function __construct(protected(set) readonly string $name, protected(set) readonly int $size, protected(set) readonly DateTimeResult $timestamp, protected(set) readonly bool $alreadyWaiting, protected(set) readonly bool $hasControlError, protected(set) readonly ?int $rowId, protected(set) readonly ?string $controlMsg)
    {
        $this->isEligibleForImport = $this->isSizeOk() && $this->isTypeOk() && !$this->isAlreadyWaiting() && !$this->hasControlError();
    }

    public function getUploaded(): string
    {
        return $this->timestamp->format('j. F Y');
    }

    public function isSizeOk(): bool
    {
        return $this->size >= self::MIN_FILESIZE && $this->size <= self::MAX_FILESIZE;
    }

    public function isTypeOk(): bool
    {
        return pathinfo($this->name, PATHINFO_EXTENSION) === self::EXTENSION;
    }

    public function isAlreadyWaiting(): bool
    {
        return $this->alreadyWaiting;
    }

    public function hasControlError(): bool
    {
        return $this->hasControlError;
    }

    public function getControlMsg(): ?string
    {
        return $this->controlMsg;
    }

    public function isEligibleToBeImported(): bool
    {
        return $this->isEligibleForImport;
    }

    public function hasPrecontrolError(): bool
    {
        return !$this->isSizeOk() ||  !$this->isTypeOk();
    }

    public function setIneligibleForImport(): self
    {
        $this->isEligibleForImport = false;

        return $this;
    }

}
