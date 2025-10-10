<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'image_download_log', schema: 'front', options: ['comment' => 'Image download log'])]
class ImageDownloadLog
{

    use TId;
    use TCreatedAt;

    #[Column(type: Types::INTEGER, nullable: false, options: ['comment' => 'ID of the photo being downloaded'])]
    protected int $photoId;

    #[Column(type: Types::STRING, nullable: false, options: ['comment' => 'Type of image being downloaded (archive, jp2, databot_thumb)'])]
    protected string $imageType;

    #[Column(type: Types::STRING, nullable: true, options: ['comment' => 'IP address of the requester'])]
    protected ?string $ipAddress = null;

    #[Column(type: Types::STRING, nullable: true, options: ['comment' => 'User agent of the requester'])]
    protected ?string $userAgent = null;

    #[Column(type: Types::STRING, nullable: true, options: ['comment' => 'Referrer URL'])]
    protected ?string $referrer = null;

    public function getPhotoId(): int
    {
        return $this->photoId;
    }

    public function setPhotoId(int $photoId): ImageDownloadLog
    {
        $this->photoId = $photoId;
        return $this;
    }

    public function getImageType(): string
    {
        return $this->imageType;
    }

    public function setImageType(string $imageType): ImageDownloadLog
    {
        $this->imageType = $imageType;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): ImageDownloadLog
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): ImageDownloadLog
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    public function setReferrer(?string $referrer): ImageDownloadLog
    {
        $this->referrer = $referrer;
        return $this;
    }
}
