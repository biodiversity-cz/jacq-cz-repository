<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'import_error', options: ['comment' => 'Errors that occur during the import'])]
// phpcs:disable SlevomatCodingStandard.Classes.SuperfluousErrorNaming.SuperfluousSuffix
class ImportError
{

// phpcs:enable
    use TId;

    #[OneToOne(targetEntity: Photos::class)]
    #[JoinColumn(name: 'photo_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE', options: ['comment' => 'photo to which this error belongs'])]
    protected Photos $photo;

    #[ManyToOne(targetEntity: Photos::class)]
    #[JoinColumn(name: 'duplicate_id', referencedColumnName: 'id', unique: false, nullable: true, options: ['comment' => 'already imported photo to which is this probably duplicity'])]
    protected ?Photos $duplicateTo;

    #[Column(type: Types::BLOB, nullable: true, options: ['comment' => 'Thumbnail during import phase'])]
    protected mixed $thumbnail;

    #[Column(type: Types::TEXT, length: 60000, unique: false, nullable: false, options: ['comment' => 'description fo the error'])]
    protected string $message = '';

    #[Column(type: Types::TEXT, length: 60000, unique: false, nullable: true, options: ['comment' => 'barcode detected in the image'])]
    protected ?string $barcodes;

    public function getPhoto(): Photos
    {
        return $this->photo;
    }

    public function setPhoto(Photos $photo): ImportError
    {
        $this->photo = $photo;

        return $this;
    }

    public function getDuplicateTo(): ?Photos
    {
        return $this->duplicateTo;
    }

    public function setDuplicateTo(?Photos $duplicateTo): ImportError
    {
        $this->duplicateTo = $duplicateTo;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): ImportError
    {
        $this->message = $message;

        return $this;
    }

    public function getBarcodes(): ?string
    {
        return $this->barcodes;
    }

    public function setBarcodes(?string $barcodes): ImportError
    {
        $this->barcodes = $barcodes;

        return $this;
    }

    public function getThumbnail(): mixed
    {
        return $this->thumbnail;
    }

    public function setThumbnail(mixed $thumbnail): ImportError
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

}
