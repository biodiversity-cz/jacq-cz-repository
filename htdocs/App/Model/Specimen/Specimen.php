<?php declare(strict_types = 1);

namespace App\Model\Specimen;

use App\Model\Database\Entity\Herbaria;
use App\Services\RepositoryConfiguration;

class Specimen
{

    protected(set) Herbaria $herbarium;

    protected(set) int $numericPartOfId;

    public function getStandardizedId(): string
    {
        return $this->herbarium->acronym . '-' . sprintf(RepositoryConfiguration::SPECIMEN_NUMERIC_FORMAT, $this->numericPartOfId);
    }

    public function setHerbarium(Herbaria $herbarium): Specimen
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function setNumericPartOfId(int $numericPartOfId): Specimen
    {
        $this->numericPartOfId = $numericPartOfId;

        return $this;
    }

}
