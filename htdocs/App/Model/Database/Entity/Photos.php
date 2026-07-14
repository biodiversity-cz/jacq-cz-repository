<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Entity\Attributes\TLastEditAt;
use App\Model\Database\Entity\Attributes\TOriginalFileAt;
use App\Model\Database\Entity\Views\VoucherVisionTranscription;
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
    public protected(set) ?string $archiveFilename = null;

    #[Column(nullable: true, options: ['comment' => 'Filename that was provided during curator upload, could make sense or completely missing semantic content'])]
    public protected(set) string $originalFilename;

    #[Column(name: 'jp2filename', unique: true, nullable: true, options: ['comment' => 'Filename of JP2 file'])]
    public protected(set) ?string $jp2Filename = null;

    #[Column(name: 'databot_thumb_filename', unique: true, nullable: true, options: ['comment' => 'Filename of PNG file devoted for Databots'])]
    public protected(set) ?string $databotThumbFilename = null;

    #[Column(options: ['comment' => 'Suffix determining bucket set where the related files are stored'])]
    public protected(set) string $bucketSuffix;

    #[ManyToOne(targetEntity: Herbaria::class, inversedBy: 'photos')]
    #[JoinColumn(name: 'herbarium_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Herbarium storing and managing the specimen data'])]
    public protected(set) Herbaria $herbarium;

    #[ManyToOne(targetEntity: PhotosStatus::class)]
    #[JoinColumn(name: 'status_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Status of the photo'])]
    public protected(set) PhotosStatus $status;

    #[Column(type: Types::STRING, nullable: true, options: ['comment' => 'Herbarium internal unique id of specimen in form without herbarium acronym'])]
    public protected(set) ?string $specimenId = null;

    #[Column(type: Types::INTEGER, nullable: true, options: ['comment' => 'Width of image with pixels'])]
    public protected(set) ?int $width = null;

    #[Column(type: Types::INTEGER, nullable: true, options: ['comment' => 'Height of image in pixels'])]
    public protected(set) ?int $height = null;

    #[Column(type: Types::BIGINT, nullable: true, options: ['comment' => 'Filesize of Archive Master TIFF file in bytes'])]
    public protected(set) ?int $archiveFileSize = null;

    #[Column(name: 'jp2file_size', type: Types::BIGINT, nullable: true, options: ['comment' => 'Filesize of converted JP2 file in bytes'])]
    public protected(set) ?int $JP2FileSize = null;

    /** @var ?mixed[] */
    #[Column(type: Types::JSON, nullable: true, options: ['jsonb' => true, 'comment' => 'raw EXIF data extracted from Archive Master file'])]
    public protected(set) ?array $exif = null;

    /** @var ?mixed[] */
    #[Column(type: Types::JSON, nullable: true, options: ['jsonb' => true, 'comment' => 'Imagick -verbose identify metadata output from the Archive Master file'])]
    public protected(set) ?array $identify = null;

    #[ManyToOne(targetEntity: PhotosType::class)]
    #[JoinColumn(name: 'type_id', referencedColumnName: 'id', nullable: false, options: ['comment' => 'Type of the photo', 'default' => 1])]
    public protected(set) PhotosType $type;
    #[ManyToOne(targetEntity: Funding::class)]
    #[JoinColumn(name: 'funding_id', referencedColumnName: 'id', nullable: true, options: ['comment' => 'Funding'])]
    public protected(set) ?Funding $funding = null;
    #[Column(type: Types::TEXT, length: 1000, unique: true, nullable: true, options: ['comment' => 'Persistent ID of photo'])]
    public protected(set) ?string $pid = null;
    #[Column(type: Types::TEXT, length: 1000, unique: false, nullable: true, options: ['comment' => 'Persistent ID of external specimen entity to which this photo belongs'])]
    public protected(set) ?string $specimenPid = null;
    #[OneToOne(targetEntity: ImportError::class, mappedBy: 'photo', cascade: ['persist', 'remove'])]
    public protected(set) ?ImportError $error = null;
    #[OneToOne(targetEntity: ImportMultiplier::class, mappedBy: 'photo', cascade: ['persist', 'remove'])]
    public protected(set) ?ImportMultiplier $multiplier = null;
    #[OneToMany(targetEntity: DatabotResult::class, mappedBy: 'photo', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public protected(set) Collection $databotResults;
    #[OneToMany(targetEntity: SpecimenMetadata::class, mappedBy: 'photo', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public protected(set) Collection $specimenMetadata;

    #[Column(name: 'embargo_timeout', type: Types::DATETIME_MUTABLE, nullable: true)]
    public protected(set) ?\DateTime $embargoTimeout = null;

    #[Column(nullable: true, options: ['comment' => 'MD5 hash of master archive file'])]
    public protected(set) ?string $archiveFileChecksum = null;

    #[OneToOne(targetEntity: VoucherVisionTranscription::class, mappedBy: 'photo')]
    public protected(set) ?VoucherVisionTranscription $transcription = null;

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
        return strtoupper($this->herbarium->acronym).'_'.$this->getSpecimenIdFixedWidth();
    }

    public function setHerbarium(Herbaria $herbarium): Photos
    {
        $this->herbarium = $herbarium;

        return $this;
    }

    public function setSpecimenId(?string $specimenId): Photos
    {
        $this->specimenId = null === $specimenId || '' === $specimenId ? null : ltrim($specimenId, '0');

        return $this;
    }

    public function getSpecimenIdFixedWidth(): string
    {
        if (ctype_digit($this->specimenId)) {
            return sprintf('%0'.$this->herbarium->digitsCount.'d', $this->specimenId);
        }

        return $this->specimenId;
    }

    public function getExpectedJacqPid(): string
    {
        return 'https://'.strtolower($this->herbarium->acronym).'.jacq.org/'.strtoupper($this->herbarium->acronym).$this->getSpecimenIdFixedWidth();
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
        // Sanitize UTF-8 encoding before JSON serialization
        if (null !== $exif) {
            $exif = $this->sanitizeArrayUtf8($exif, 'exif');
        }
        $this->exif = $exif;

        return $this;
    }

    /**
     * @param ?mixed[] $identify
     */
    public function setIdentify(?array $identify): Photos
    {
        // Sanitize UTF-8 encoding before JSON serialization
        if (null !== $identify) {
            $identify = $this->sanitizeArrayUtf8($identify, 'identify');
        }
        $this->identify = $identify;

        return $this;
    }

    /**
     * Recursively sanitize string values in an array to ensure valid UTF-8 encoding.
     * Invalid UTF-8 sequences are converted to valid UTF-8.
     *
     * @param mixed[] $array
     * @return mixed[]
     */
    protected function sanitizeArrayUtf8(array $array, string $fieldName, string $path = ''): array
    {
        foreach ($array as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : (string) $key;

            if (is_array($value)) {
                $array[$key] = $this->sanitizeArrayUtf8($value, $fieldName, $currentPath);
            } elseif (is_string($value)) {
                if (!mb_check_encoding($value, 'UTF-8')) {
                    // Convert from ISO-8859-1 (Latin-1) to UTF-8
                    $converted = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
                    if (false !== $converted && mb_check_encoding($converted, 'UTF-8')) {
                        $array[$key] = $converted;
                    } else {
                        // If conversion fails, remove invalid bytes
                        $array[$key] = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
                    }
                }
            }
        }

        return $array;
    }

    public function addImportError(): ImportError
    {
        if (null === $this->error) {
            $this->error = new ImportError();
            $this->error->setPhoto($this);
        }

        return $this->error;
    }

    public function removeImportError(): void
    {
        if (null !== $this->error) {
            $this->error = null;
        }
    }

    public function addMultiplier(): ImportMultiplier
    {
        if (null === $this->multiplier) {
            $this->multiplier = new ImportMultiplier()->setPhoto($this);
        }

        return $this->multiplier;
    }

    public function removeMultiplier(): Photos
    {
        if (null !== $this->multiplier) {
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
        if (ExternalDatabase::JACQ === $externalDatabase->id) {
            return $baseurl.rawurlencode($this->getExpectedJacqPid());
        }
        if (ExternalDatabase::INTERNAL === $externalDatabase->id) {
            // TODO zde může být problém s oddělovačem mezi acronymem a id - záleží co Museion exporter generuje - nyní dává mezeru..
            $params = [
                'barcode' => strtoupper($this->herbarium->acronym).' '.$this->specimenId,
                'herbariumId' => $this->herbarium->id,
            ];

            return $baseurl.'?'.http_build_query($params);
        }

        return $baseurl.$this->getFullSpecimenId();
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

    public function getDatabotOkResultById(Databot $databot): ?DatabotResult
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('databot', $databot))
            ->andWhere(Criteria::expr()->eq('status', 'ok'))
            ->setMaxResults(1);

        return $this->databotResults->matching($criteria)->first() ?: null;
    }
}
