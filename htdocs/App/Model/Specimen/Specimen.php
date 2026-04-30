<?php declare(strict_types=1);

namespace App\Model\Specimen;

use App\Model\Database\Entity\Herbaria;

class Specimen
{

    protected(set) Herbaria $herbarium;

    protected(set) string $id;

    public function getStandardizedId(): string
    {
        if (ctype_digit($this->id)) {
            return $this->herbarium->acronym . '-' . sprintf('%0' . $this->herbarium->digitsCount . 'd', $this->id);
        }
        return $this->herbarium->acronym . '-' . $this->id;

    }

    public function setHerbarium(Herbaria $herbarium): Specimen
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function setId(string $id): Specimen
    {
        $this->id = $id;

        return $this;
    }

}
