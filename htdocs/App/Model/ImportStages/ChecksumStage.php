<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\ConvertStageException;
use League\Pipeline\StageInterface;

class ChecksumStage extends BaseStage implements StageInterface
{
    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        try {
            /** @var Photos $payload */
            $payload->setArchiveFileChecksum(md5_file($this->getMasterTempPath()));
        } catch (\Throwable $exception) {
            throw new ConvertStageException('unable compute MD5 checksum (' . $exception->getMessage() . '): ' . $payload->id);
        }

        return $payload;
    }

}
