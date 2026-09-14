<?php

declare(strict_types=1);

namespace App\Model\Specimen;

use App\Model\Database\Entity\Herbaria;

class Specimen
{
    public protected(set) Herbaria $herbarium;

    public protected(set) string $id;

    /**
     * duplicate code with \App\Model\Database\Entity\Photos::getSpecimenIdFixedWidth
     */
    public function getStandardizedId(): string
    {
        if (ctype_digit($this->id) || $this->herbarium->alwaysTrailingZeros) {
            return $this->herbarium->acronym.'-'.str_pad(
                $this->id,
                $this->herbarium->digitsCount,
                '0',
                STR_PAD_LEFT
            );
        }

        return $this->herbarium->acronym.'-'.$this->id;
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
