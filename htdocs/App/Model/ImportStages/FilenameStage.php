<?php declare(strict_types=1);

namespace App\Model\ImportStages;

use App\Model\Database\Entity\Photos;
use App\Model\ImportStages\Exceptions\FilenameStageException;
use App\Services\SpecimenIdService;
use League\Pipeline\StageInterface;

class FilenameStage implements StageInterface
{

    protected Photos $item;

    public function __construct(protected readonly SpecimenIdService $specimenIdService)
    {
    }

    public function __invoke(mixed $payload): mixed
    {
        $this->item = $payload;
        /**
         * skip detection when manually inserted id
         */
        if ($this->item->getSpecimenId() === null) {
            $this->checkFilename();
        }

        return $this->item;

    }

    protected function checkFilename(): void
    {
        $parts = [];

        if (!preg_match($this->item->getHerbarium()->getRegexFilename(), $this->item->getOriginalFilename(),$parts)) {
            throw new FilenameStageException('Invalid filename');
        }
        $this->item->setSpecimenId($parts[SpecimenIdService::regexSpecimenPart]);
    }

}
