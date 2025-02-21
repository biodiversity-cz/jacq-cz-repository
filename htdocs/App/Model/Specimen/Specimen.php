<?php declare(strict_types=1);

namespace App\Model\Specimen;

use App\Model\Database\Entity\Herbaria;
use App\Services\RepositoryConfiguration;

class Specimen
{

    protected Herbaria $herbarium;

    protected int $numericPartOfId;

    public function __construct()
    {
    }

    public function getStandardizedId()
    {
        return $this->getHerbarium()->getAcronym() . '-' . sprintf(RepositoryConfiguration::SPECIMEN_NUMERIC_FORMAT, $this->getNumericPartOfId());

    }

    public function getHerbarium(): Herbaria
    {
        return $this->herbarium;
    }

    public function setHerbarium(Herbaria $herbarium): Specimen
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function getNumericPartOfId(): int
    {
        return $this->numericPartOfId;
    }

    public function setNumericPartOfId(int $numericPartOfId): Specimen
    {
        $this->numericPartOfId = $numericPartOfId;

        return $this;
    }

}
