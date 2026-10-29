<?php

declare(strict_types=1);

namespace App\Services;

class AppDir
{
    protected string $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    public function getPath(string $fromBaseDir = ''): string
    {
        return $this->dir.DIRECTORY_SEPARATOR.$fromBaseDir;
    }
}
