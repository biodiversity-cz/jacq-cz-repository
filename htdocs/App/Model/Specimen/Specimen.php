<?php

declare(strict_types=1);

namespace App\Model\Specimen;

use App\Model\Database\Entity\Herbaria;

class Specimen
{
    public protected(set) Herbaria $herbarium;

    public protected(set) string $id;

    /**
     * duplicate code logic with \App\Model\Database\Entity\Photos::getSpecimenIdFixedWidth.
     */
    public function getStandardizedId(): string
    {
        if (
            ctype_digit($this->id) || $this->herbarium->digitsCountOnSubstring
        ) {
            if (preg_match('/^\d+/', $this->id, $matches)) {
                $numericPart = $matches[0];

                if (strlen($numericPart) < $this->herbarium->digitsCount) {
                    return $this->herbarium->acronym.'-'.str_pad(
                            $numericPart,
                            $this->herbarium->digitsCount,
                            '0',
                            STR_PAD_LEFT
                        ) . substr($this->id, strlen($numericPart));
                }
            }
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
