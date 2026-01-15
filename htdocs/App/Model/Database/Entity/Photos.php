<?php declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Entity\Attributes\TOriginalFileAt;
use App\Model\Database\Repository\PhotosRepository;
use App\Services\Exceptions\RiskOfPidOverwritten;
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
    protected(set) ?string $archiveFilename = null;

    #[Column(nullable: true, options: ['comment' => 'Filename that was provided during curator upload, could make sense or completely missing semantic content'])]
    protected(set) string $originalFilename;

    #[Column(name: 'jp2filename', unique: true, nullable: true, options: ['comment' => 'Filename of JP2 file'])]
    protected(set) ?string $jp2Filename = null;

    #[Column(name: 'databot_thumb_filename', unique: true, nullable: true, options: ['comment' => 'Filename of PNG file devoted for Databots'])]
    protected(set) ?string $databotThumbFilename = null;

    #[Column(options: ['comment' => 'Suffix determining bucket set where the related files are stored'])]
    protected(set) string $bucketSuffix;

    #[ManyToOne(targetEntity: Herbaria::class, inversedBy: 'photos')]
    #[JoinColumn(name: 'herbarium_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Herbarium storing and managing the specimen data'])]
    protected(set) Herbaria $herbarium;

    #[ManyToOne(targetEntity: PhotosStatus::class)]
    #[JoinColumn(name: 'status_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Status of the photo'])]
    protected(set) PhotosStatus $status;

    #[Column(type: Types::STRING, nullable: true, options: ['comment' => 'Herbarium internal unique id of specimen in form without herbarium acronym'])]
    protected(set) ?string $specimenId = null;

    #[Column(type: Types::INTEGER, nullable: true, options: ['comment' => 'Width of image with pixels'])]
    protected(set) ?int $width = null;

    #[Column(type: Types::INTEGER, nullable: true, options: ['comment' => 'Height of image in pixels'])]
    protected(set) ?int $height = null;

    #[Column(type: Types::BIGINT, nullable: true, options: ['comment' => 'Filesize of Archive Master TIFF file in bytes'])]
    protected(set) ?int $archiveFileSize = null;

    #[Column(name: 'jp2file_size', type: Types::BIGINT, nullable: true, options: ['comment' => 'Filesize of converted JP2 file in bytes'])]
    protected(set) ?int $JP2FileSize = null;

    /** @var ?mixed[] */
    #[Column(type: Types::JSON, nullable: true, options: ['jsonb' => true, 'comment' => 'raw EXIF data extracted from Archive Master file'])]
    protected(set) ?array $exif = null;

    /** @var ?mixed[] */
    #[Column(type: Types::JSON, nullable: true, options: ['jsonb' => true, 'comment' => 'Imagick -verbose identify metadata output from the Archive Master file'])]
    protected(set) ?array $identify = null;

    #[ManyToOne(targetEntity: PhotosType::class)]
    #[JoinColumn(name: 'type_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Type of the photo', 'default' => 1])]
    protected(set) PhotosType $type;
    #[ManyToOne(targetEntity: Funding::class)]
    #[JoinColumn(name: 'funding_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'Funding'])]
    protected(set) ?Funding $funding = null;
    #[Column(type: Types::TEXT, length: 1000, unique: true, nullable: true, options: ['comment' => 'Persistent ID of photo'])]
    protected(set) ?string $pid = null;
    #[Column(type: Types::TEXT, length: 1000, unique: false, nullable: true, options: ['comment' => 'Persistent ID of external specimen entity to which this photo belongs'])]
    protected(set) ?string $specimenPid = null;
    #[OneToOne(targetEntity: ImportError::class, mappedBy: 'photo', cascade: ['persist', 'remove'])]
    protected(set) ?ImportError $error = null;
    #[OneToOne(targetEntity: ImportMultiplier::class, mappedBy: 'photo', cascade: ['persist', 'remove'])]
    protected(set) ?ImportMultiplier $multiplier = null;
    #[OneToMany(targetEntity: DatabotResult::class, mappedBy: 'photo', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected(set) Collection $databotResults;
    #[OneToMany(targetEntity: SpecimenMetadata::class, mappedBy: 'photo', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected(set) Collection $specimenMetadata;

    #[Column(name: 'embargo_timeout', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected(set) ?\DateTime $embargoTimeout = null;

    #[Column(nullable: true, options: ['comment' => 'MD5 hash of master archive file'])]
    protected(set) ?string $archiveFileChecksum = null;

    public function __construct()
    {
        $this->databotResults = new ArrayCollection();
        $this->specimenMetadata = new ArrayCollection();
    }

    public function setArchiveFilename(string $archiveFilename): Photos
    {
        $this->archiveFilename = $archiveFilename;

        return $this;
    }

    public function setJp2Filename(string $jp2Filename): Photos
    {
        $this->jp2Filename = $jp2Filename;

        return $this;
    }

    public function setWidth(?int $width): Photos
    {
        $this->width = $width;

        return $this;
    }

    public function setHeight(?int $height): Photos
    {
        $this->height = $height;

        return $this;
    }

    public function setArchiveFileSize(?int $archiveFileSize): Photos
    {
        $this->archiveFileSize = $archiveFileSize;

        return $this;
    }

    public function setJp2FileSize(?int $JP2FileSize): Photos
    {
        $this->JP2FileSize = $JP2FileSize;

        return $this;
    }

    public function getFullSpecimenId(): string
    {
        return strtoupper($this->herbarium->acronym) . '_' . $this->getSpecimenIdFixedWidth();
    }
    public function setHerbarium(Herbaria $herbarium): Photos
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function setSpecimenId(?string $specimenId): Photos
    {
        $this->specimenId = $specimenId === null || $specimenId === '' ? null : ltrim($specimenId, '0');

        return $this;
    }

    public function getSpecimenIdFixedWidth():string
    {
        return sprintf('%0'.$this->herbarium->digitsCount.'d', $this->specimenId);
    }

    public function getExpectedJacqPid(): string
    {
        return 'https://' . strtolower($this->herbarium->acronym) . '.jacq.org/' . strtoupper($this->herbarium->acronym) . $this->getSpecimenIdFixedWidth();
    }

    public function setStatus(PhotosStatus $status): Photos
    {
        $this->status = $status;

        return $this;
    }

    public function setOriginalFilename(string $originalFilename): Photos
    {
        $this->originalFilename = $originalFilename;

        return $this;
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
     * @param ?mixed[] $identify
     */
    public function setIdentify(?array $identify): Photos
    {
        $this->identify = $identify;

        return $this;
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

    public function setType(PhotosType $type): Photos
    {
        $this->type = $type;

        return $this;
    }
    public function setDatabotResults(Collection $databotResults): Photos
    {
        $this->databotResults = $databotResults;
        return $this;
    }

    public function setDatabotThumbFilename(?string $databotThumbFilename): Photos
    {
        $this->databotThumbFilename = $databotThumbFilename;
        return $this;
    }

    public function setBucketSuffix(string $bucketSuffix): Photos
    {
        $this->bucketSuffix = $bucketSuffix;
        return $this;
    }

    public function isPublic(): bool
    {
        return in_array($this->status->id, [PhotosStatus::PUBLISHED], true);
    }

    public function getLatestSpecimenMetadata(): ?SpecimenMetadata
    {
        $metadata = $this->getSpecimenMetadata();
        return $metadata->first() ?: null;
    }

    public function getSpecimenMetadata(): Collection
    {
        $criteria = Criteria::create()
            ->orderBy(['timestamp' => Order::Descending]);

        return $this->specimenMetadata->matching($criteria);
    }

    public function setFunding(?Funding $funding): Photos
    {
        $this->funding = $funding;
        return $this;
    }

    public function setPid(string $pid): Photos
    {
        if (!empty($this->pid)) {
            throw new RiskOfPidOverwritten();
        }
        $this->pid = $pid;
        return $this;
    }
    public function setSpecimenPid(?string $specimenPid): Photos
    {
        $this->specimenPid = $specimenPid;
        return $this;
    }

    public function getSpecimenPidApiEndpoint(): string
    {
        $externalDatabase = $this->herbarium->externalDatabase;
        $baseurl = $externalDatabase->url;
        if ($externalDatabase->id === ExternalDatabase::JACQ) {
            return $baseurl . rawurlencode($this->getExpectedJacqPid());
        }
        if ($externalDatabase->id === ExternalDatabase::INTERNAL) {
            return $baseurl . strtoupper($this->herbarium->acronym) . '_' . $this->specimenId . '?herbariumId=' . $this->herbarium->id;
        }

        return $baseurl . $this->getFullSpecimenId();
    }

    public function setEmbargoTimeout(): Photos
    {
        $this->embargoTimeout = new \DateTime('+2 years');
        return $this;
    }

    public function dropEmbargoTimeout(): Photos
    {
        $this->embargoTimeout = null;
        return $this;
    }

    public function setArchiveFileChecksum(?string $archiveFileChecksum): Photos
    {
        $this->archiveFileChecksum = $archiveFileChecksum;
        return $this;
    }

}
