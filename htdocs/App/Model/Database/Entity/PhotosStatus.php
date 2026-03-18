<?php declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Repository\PhotosStatusRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: PhotosStatusRepository::class)]
#[Table(name: 'photos_status', options: ['comment' => 'List of allowed photo statuses'])]
class PhotosStatus
{

    use TId;

    public const int WAITING = 1;
    public const int IMAGE_CONTROL_ERROR = 2;
    public const int IMAGE_CONTROL_OK = 3;
    public const int PUBLISHED = 4;
    public const int EMBARGO = 5;
    public const int SPECIMEN_CONTROL_OK = 6;
    public const int WAITING_FOR_PUBLISHING = 7;
    public const int DEVELOP_PROCEED = 100;
    public const array PASSED = [self::IMAGE_CONTROL_OK, self::SPECIMEN_CONTROL_OK, self::WAITING_FOR_PUBLISHING, self::EMBARGO];
    public const array DELETEABLE = [self::IMAGE_CONTROL_ERROR, self::IMAGE_CONTROL_OK, self::SPECIMEN_CONTROL_OK, self::EMBARGO];
    public const array EMBARGOABLE = [self::SPECIMEN_CONTROL_OK, self::EMBARGO];
    public const array WITH_CETAF_SPECIMEN = [self::SPECIMEN_CONTROL_OK, self::WAITING_FOR_PUBLISHING, self::EMBARGO, self::PUBLISHED];
    public const array WITHOUT_CETAF_SPECIMEN = [self::IMAGE_CONTROL_OK, self::IMAGE_CONTROL_ERROR, self::WAITING];


    #[Column(unique: true, nullable: false, options: ['comment' => 'name of the status'])]
    protected(set) string $name;

    #[Column(unique: true, nullable: false, options: ['comment' => 'short description'])]
    protected(set) string $description;

    #[Column(nullable: false, options: ['comment' => 'CSS color class for status visualisation', 'default' => 'primary'])]
    protected(set) string $color;

    #[Column(unique: true, nullable: false, options: ['comment' => 'how to order statuses when presented, not necessary the only succession that exists'])]
    protected(set) int $succession;


    public function setColor(string $color): PhotosStatus
    {
        $this->color = $color;

        return $this;
    }

    public function setName(string $name): PhotosStatus
    {
        $this->name = $name;
        return $this;
    }

    public function setDescription(string $description): PhotosStatus
    {
        $this->description = $description;
        return $this;
    }

    public function setSuccession(int $succession): PhotosStatus
    {
        $this->succession = $succession;
        return $this;
    }

}
