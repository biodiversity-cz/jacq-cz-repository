<?php

declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Bootstrap;
use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\BaseStage;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\TempDir;
use Tester\Assert;
use Tests\Toolkit\PhotoTestFactory;

require_once __DIR__.'/../../../../bootstrap.php';

class TestStage extends BaseStage
{
    public function setItem(Photos $item): void
    {
        $this->item = $item;
    }

    public function callGetDatabotThumbTempPath(): string
    {
        return $this->getDatabotThumbTempPath();
    }

    public function callGetZbarThumbTempPath(): string
    {
        return $this->getZbarThumbTempPath();
    }

    public function callGetIiifTempPath(): string
    {
        return $this->getIiifTempPath();
    }

    public function callGetMasterTempPath(): string
    {
        return $this->getMasterTempPath();
    }

    public function callGetDuplicateTempPath(Photos $photo): string
    {
        return $this->getDuplicateTempPath($photo);
    }

    public function __invoke(mixed $payload): mixed
    {
        return null;
    }
}

test('BaseStage temp paths are correct', function (): void {
    $photo = PhotoTestFactory::minimal();

    $tempDir = \Mockery::mock(TempDir::class);
    $tempDir->shouldReceive('getPath')->andReturnUsing(fn ($filename) => '/tmp/'.$filename);

    $container = Bootstrap::boot()->createContainer();
    $repoConfig = $container->getByType(RepositoryConfiguration::class);
    $imagickService = \Mockery::mock(ImagickService::class);

    $stage = new TestStage($tempDir, $repoConfig, $imagickService);
    $stage->setItem($photo);

    Assert::same('/tmp/databot.png', $stage->callGetDatabotThumbTempPath());
    Assert::same('/tmp/zbar.png', $stage->callGetZbarThumbTempPath());
    Assert::same('/tmp/iiif.jp2', $stage->callGetIiifTempPath());
    Assert::same('/tmp/archive.tif', $stage->callGetMasterTempPath());
    Assert::same('/tmp/duplicate.tif', $stage->callGetDuplicateTempPath($photo));
});
