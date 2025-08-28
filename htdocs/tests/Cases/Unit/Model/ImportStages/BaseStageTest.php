<?php declare(strict_types=1);

namespace Tests\Cases\Unit\Model\Database\Entity;

use App\Bootstrap;
use App\Model\Database\Entity\Herbaria;
use App\Model\Database\Entity\Photos;
use App\Model\IIIF\ManifestFactory;
use App\Model\ImportStages\BaseStage;
use App\Model\Specimen\Specimen;
use App\Services\EntityServices\PhotoService;
use App\Services\ImagickService;
use App\Services\RepositoryConfiguration;
use App\Services\TempDir;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Application\LinkGenerator;
use Tester\Assert;

require_once __DIR__ . '/../../../../bootstrap.php';


class TestStage extends BaseStage {
    public function setItem(Photos $item): void {
        $this->item = $item;
    }

    public function callGetDatabotThumbTempPath(): string {
        return $this->getDatabotThumbTempPath();
    }

    public function callGetZbarThumbTempPath(): string {
        return $this->getZbarThumbTempPath();
    }

    public function callGetIiifTempPath(): string {
        return $this->getIiifTempPath();
    }

    public function callGetMasterTempPath(): string {
        return $this->getMasterTempPath();
    }

    public function callGetDuplicateTempPath(Photos $photo): string {
        return $this->getDuplicateTempPath($photo);
    }
    public function __invoke(mixed $payload): mixed
    {return null;}
}

test('BaseStage temp paths are correct', function(): void {
    $photo = \Mockery::mock(Photos::class);
    $photo->shouldReceive('getId')->andReturn(42);
    $photo->shouldReceive('getOriginalFilename')->andReturn('image.tiff');

    $tempDir = \Mockery::mock(TempDir::class);
    $tempDir->shouldReceive('getPath')->andReturnUsing(fn($filename) => '/tmp/' . $filename);

    $container = Bootstrap::boot()->createContainer();
    $repoConfig = $container->getByType(RepositoryConfiguration::class);
    $imagickService = \Mockery::mock(ImagickService::class);

    $stage = new TestStage($tempDir, $repoConfig, $imagickService);
    $stage->setItem($photo);

    Assert::same('/tmp/databot_42.png', $stage->callGetDatabotThumbTempPath());
    Assert::same('/tmp/zbar_42.png', $stage->callGetZbarThumbTempPath());
    Assert::same('/tmp/iiif_42jp2', $stage->callGetIiifTempPath());
    Assert::same('/tmp/archive_42.tiff', $stage->callGetMasterTempPath());
    Assert::same('/tmp/duplicate_42.tiff', $stage->callGetDuplicateTempPath($photo));
});
