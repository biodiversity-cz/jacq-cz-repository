<?php

declare(strict_types=1);

namespace App\Model\FileManagement;

use Aws\Api\DateTimeResult;

class FileInsideCuratorBucket
{
    public const int MAX_FILESIZE = 650 * 1024 * 1024;

    public const array EXTENSION = ['tif', 'tiff'];
    public const string MIME_TYPE = 'image/tiff';

    protected bool $isEligibleForImport = false;

    public function __construct(public protected(set) readonly string $name, public protected(set) readonly int $size, public protected(set) readonly int $minFileSize, public protected(set) readonly DateTimeResult $timestamp, public protected(set) readonly bool $alreadyWaiting, public protected(set) readonly bool $hasControlError, public protected(set) readonly ?int $rowId, public protected(set) readonly ?string $controlMsg)
    {
        $this->isEligibleForImport = $this->isSizeOk() && $this->isTypeOk() && !$this->isAlreadyWaiting() && !$this->hasControlError();
    }

    public function getUploaded(): string
    {
        return $this->timestamp->format('j. F Y');
    }

    public function isSizeOk(): bool
    {
        return $this->size >= $this->minFileSize && $this->size <= self::MAX_FILESIZE;
    }

    public function isTypeOk(): bool
    {
        return in_array(
            strtolower(pathinfo($this->name, PATHINFO_EXTENSION)),
            self::EXTENSION,
            true
        );
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
        return !$this->isSizeOk() || !$this->isTypeOk();
    }
}
