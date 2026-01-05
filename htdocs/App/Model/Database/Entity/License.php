<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity]
#[Table(name: 'license', options: ['comment' => 'Licenses available in the repository'])]
class License
{

    use TId;

    #[Column(unique: true, nullable: false, options: ['comment' => 'acronym'])]
    protected(set) string $acronym;

    #[Column(type: Types::TEXT, unique: true, nullable: false, options: ['comment' => 'link to full text'])]
    protected(set) string $link;

    #[Column(name: 'is_default', type: 'boolean', nullable: false, options: ['default' => false])]
    protected(set) bool $default = false;

    public function setAcronym(string $acronym): License
    {
        $this->acronym = $acronym;
        return $this;
    }

    public function setLink(string $link): License
    {
        $this->link = $link;
        return $this;
    }
    public function setDefault(bool $default): License
    {
        $this->default = $default;
        return $this;
    }
}
