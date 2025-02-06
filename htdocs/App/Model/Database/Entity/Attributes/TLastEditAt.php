<?php declare(strict_types = 1);

namespace App\Model\Database\Entity\Attributes;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PreUpdate;

trait TLastEditAt
{

    #[Column(name: 'lastedit_timestamp', type: Types::DATETIME_MUTABLE, nullable: false, options: ["default"=>"CURRENT_TIMESTAMP"])]
    protected ?DateTime $lastEdit = null;

    public function getLastEditAt(): DateTime
    {
        return $this->lastEdit;
    }

    #[PreUpdate]
    public function setLastEditAt(): mixed
    {
        $this->lastEdit = new DateTime();

        return $this;
    }

}
