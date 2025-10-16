<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Entity\Attributes\TOriginalFileAt;
use App\Model\Database\Repository\PhotosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: PhotosRepository::class)]
#[Table(name: 'photos', options: ['comment' => 'Specimen photos'])]
class Photos
{

    use TId;
    use TCreatedAt;
    use TLastEditAt;
    use TOriginalFileAt;

    #[Column(unique: true, nullable: true, options: ['comment' => 'Filename of Archive Master TIF file'])]
    protected ?string $archiveFilename = null;

    #[Column(nullable: true, options: ['comment' => 'Filename that was provided during curator upload, could make sense or completely missing semantic content'])]
    protected string $originalFilename;

    #[Column(name: 'jp2filename', unique: true, nullable: true, options: ['comment' => 'Filename of JP2 file'])]
    protected ?string $jp2Filename = null;

    #[Column(name: 'databot_thumb_filename', unique: true, nullable: true, options: ['comment' => 'Filename of PNG file devoted for Databots'])]
    protected ?string $databotThumbFilename = null;

    #[ManyToOne(targetEntity: Herbaria::class, inversedBy: 'photos')]
    #[JoinColumn(name: 'herbarium_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Herbarium storing and managing the specimen data'])]
    protected Herbaria $herbarium;

    #[ManyToOne(targetEntity: PhotosStatus::class)]
    #[JoinColumn(name: 'status_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Status of the photo'])]
    protected PhotosStatus $status;

    #[Column(type: Types::STRING, nullable: true, options: ['comment' => 'Herbarium internal unique id of specimen in form without herbarium acronym'])]
    protected ?string $specimenId = null;

    #[Column(type: Types::INTEGER, nullable: true, options: ['comment' => 'Width of image with pixels'])]
    protected ?int $width = null;

    #[Column(type: Types::INTEGER, nullable: true, options: ['comment' => 'Height of image in pixels'])]
    protected ?int $height = null;

    #[Column(type: Types::BIGINT, nullable: true, options: ['comment' => 'Filesize of Archive Master TIFF file in bytes'])]
    protected ?int $archiveFileSize = null;

    #[Column(name: 'jp2file_size', type: Types::BIGINT, nullable: true, options: ['comment' => 'Filesize of converted JP2 file in bytes'])]
    protected ?int $JP2FileSize = null;

    /** @var ?mixed[] */
    #[Column(type: Types::JSON, nullable: true, options: ['jsonb' => true, 'comment' => 'raw EXIF data extracted from Archive Master file'])]
    protected ?array $exif = null;

    /** @var ?mixed[] */
    #[Column(type: Types::JSON, nullable: true, options: ['jsonb' => true, 'comment' => 'Imagick -verbose identify metadata output from the Archive Master file'])]
    protected ?array $identify = null;

    #[ManyToOne(targetEntity: PhotosType::class)]
    #[JoinColumn(name: 'type_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Type of the photo', 'default' => 1])]
    protected PhotosType $type;

    #[OneToOne(targetEntity: ImportError::class, mappedBy: 'photo', cascade: ['persist', 'remove'])]
    private ?ImportError $error = null;

    #[OneToOne(targetEntity: ImportMultiplier::class, mappedBy: 'photo', cascade: ['persist', 'remove'])]
    private ?ImportMultiplier $multiplier = null;

    #[OneToMany(targetEntity: DatabotResult::class, mappedBy: 'photo', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $databotResults;

    #[OneToMany(targetEntity: SpecimenMetadata::class, mappedBy: 'photo', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $specimenMetadata;

    #[ManyToOne(targetEntity: Funding::class)]
    #[JoinColumn(name: 'funding_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'Funding'])]
    protected ?Funding $funding = null;

    public function __construct()
    {
        $this->databotResults = new ArrayCollection();
        $this->specimenMetadata = new ArrayCollection();
    }

    public function getArchiveFilename(): ?string
    {
        return $this->archiveFilename;
    }

    public function setArchiveFilename(string $archiveFilename): Photos
    {
        $this->archiveFilename = $archiveFilename;

        return $this;
    }

    public function getJp2Filename(): ?string
    {
        return $this->jp2Filename;
    }

    public function setJp2Filename(string $jp2Filename): Photos
    {
        $this->jp2Filename = $jp2Filename;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): Photos
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): Photos
    {
        $this->height = $height;

        return $this;
    }

    public function getArchiveFileSize(): ?int
    {
        return $this->archiveFileSize;
    }

    public function setArchiveFileSize(?int $archiveFileSize): Photos
    {
        $this->archiveFileSize = $archiveFileSize;

        return $this;
    }

    public function getJp2FileSize(): ?int
    {
        return $this->JP2FileSize;
    }

    public function setJp2FileSize(?int $JP2FileSize): Photos
    {
        $this->JP2FileSize = $JP2FileSize;

        return $this;
    }

    public function getFullSpecimenId(): string
    {
        return strtoupper($this->getHerbarium()->getAcronym()) . '_' . sprintf('%06d', $this->getSpecimenId());
    }

    public function getHerbarium(): Herbaria
    {
        return $this->herbarium;
    }

    public function setHerbarium(Herbaria $herbarium): Photos
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function getSpecimenId(): ?string
    {
        return $this->specimenId;
    }

    public function setSpecimenId(?string $specimenId): Photos
    {
        $this->specimenId = $specimenId === null || $specimenId === '' ? null : ltrim($specimenId, '0');

        return $this;
    }

    public function getJacqPid(): string
    {
        return 'https://' . strtolower($this->getHerbarium()->getAcronym()) . '.jacq.org/' . strtoupper($this->getHerbarium()->getAcronym()) . $this->getSpecimenId();
    }

    public function getStatus(): PhotosStatus
    {
        return $this->status;
    }

    public function setStatus(PhotosStatus $status): Photos
    {
        $this->status = $status;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): Photos
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * @return ?mixed[]
     */
    public function getExif(): ?array
    {
        return $this->exif;
    }

    /**
     * @param ?mixed[] $exif
     */
    public function setExif(?array $exif): Photos
    {
        $this->exif = $exif;

        return $this;
    }

    /**
     * @return ?mixed[]
     */
    public function getIdentify(): ?array
    {
        return $this->identify;
    }

    /**
     * @param ?mixed[] $identify
     */
    public function setIdentify(?array $identify): Photos
    {
        $this->identify = $identify;

        return $this;
    }

    public function getError(): ?ImportError
    {
        return $this->error;
    }

    public function addImportError(): ImportError
    {
        if ($this->error === null) {
            $this->error = new ImportError();
            $this->error->setPhoto($this);
        }

        return $this->error;
    }

    public function removeImportError(): void
    {
        if ($this->error !== null) {
            $this->error = null;
        }
    }

    public function getMultiplier(): ?ImportMultiplier
    {
        return $this->multiplier;
    }

    public function addMultiplier(): ImportMultiplier
    {
        if ($this->multiplier === null) {
            $this->multiplier = new ImportMultiplier()->setPhoto($this);
        }

        return $this->multiplier;
    }

    public function removeMultiplier(): Photos
    {
        if ($this->multiplier !== null) {
            $this->multiplier = null;
        }
        return $this;
    }

    public function getType(): PhotosType
    {
        return $this->type;
    }

    public function setType(PhotosType $type): Photos
    {
        $this->type = $type;

        return $this;
    }

    public function getDatabotResults(): Collection
    {
        return $this->databotResults;
    }

    public function setDatabotResults(Collection $databotResults): Photos
    {
        $this->databotResults = $databotResults;
        return $this;
    }

    public function getDatabotThumbFilename(): ?string
    {
        return $this->databotThumbFilename;
    }

    public function setDatabotThumbFilename(?string $databotThumbFilename): Photos
    {
        $this->databotThumbFilename = $databotThumbFilename;
        return $this;
    }

    public function isPublic(): bool
    {
        return in_array($this->status->getId(), [PhotosStatus::PUBLIC], true);
    }

    public function getSpecimenMetadata(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['timestamp' => Order::Descending]);

        return $this->specimenMetadata->matching($criteria);
    }

    public function getLatestSpecimenMetadata(): ?SpecimenMetadata
    {
        $metadata = $this->getSpecimenMetadata();
        return $metadata->first() ?: null;
    }

    public function getFunding(): ?Funding
    {
        return $this->funding;
    }

    public function setFunding(?Funding $funding): Photos
    {
        $this->funding = $funding;
        return $this;
    }

}
