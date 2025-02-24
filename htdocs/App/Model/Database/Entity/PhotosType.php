<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Repository\PhotosTypeRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: PhotosTypeRepository::class)]
#[Table(name: 'photos_type', options: ['comment' => 'Types of images (like preserved specimen or photo from the field)'])]
class PhotosType
{

    use TId;

    #[Column(unique: true, nullable: false, options: ['comment' => 'name of the type'])]
    protected string $name;

    #[Column(unique: true, nullable: false, options: ['comment' => 'short description'])]
    protected string $description;

    #[Column(unique: true, nullable: false, options: ['comment' => 'CSS color class for status visualisation', 'default' => 'primary'])]
    protected string $color;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): PhotosType
    {
        $this->color = $color;
        return $this;
    }



}
