<?php

declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Repository\HerbariaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;

#[Entity(repositoryClass: HerbariaRepository::class)]
#[Table(name: 'herbaria', options: ['comment' => 'List of involved herbaria'])]
class Herbaria
{
    use TId;

    #[ManyToOne(targetEntity: ExternalDatabase::class)]
    #[JoinColumn(name: 'external_database_id', referencedColumnName: 'id', nullable: false)]
    public protected(set) ExternalDatabase $externalDatabase;

    #[Column(unique: true, nullable: false, options: ['comment' => 'Acronym of herbarium according to Index Herbariorum'])]
    public protected(set) string $acronym;

    #[Column(unique: true, nullable: false, options: ['comment' => 'S3 bucket where are stored new images before imported to the repository'])]
    public protected(set) string $bucket;

    #[Column(unique: false, nullable: false, options: ['comment' => 'RegEx for barcode on the specimen'])]
    public protected(set) string $regexBarcode;

    #[Column(unique: false, nullable: false, options: ['comment' => 'RegEx for image filenames'])]
    public protected(set) string $regexFilename;
    #[Column(unique: false, nullable: false, options: ['comment' => 'Allow use filename when barcode is not present in the image', 'default' => false])]
    public protected(set) bool $fallbackFilename = false;

    #[Column(unique: false, nullable: false, options: ['comment' => 'When multiple valid barcodes are present, multiply image to all these IDs', 'default' => false])]
    public protected(set) bool $multipleBarcodeMultiplier = false;

    #[Column(unique: false, nullable: false, options: ['comment' => 'Require herbarium acronym on the start of the barcode to be accepted as valid', 'default' => true])]
    public protected(set) bool $strictBarcodeAcronymPrefix = true;

    #[Column(type: Types::TEXT, length: 5000, unique: false, nullable: true, options: ['comment' => 'logo URL'])]
    public protected(set) ?string $logo = null;

    #[Column(type: Types::TEXT, length: 5000, unique: false, nullable: true, options: ['comment' => 'full name of the herbarium'])]
    public protected(set) ?string $fullname = null;

    #[Column(type: Types::TEXT, length: 5000, unique: false, nullable: true, options: ['comment' => 'address of the institution/herbarium'])]
    public protected(set) ?string $address = null;

    #[OneToMany(targetEntity: Photos::class, mappedBy: 'herbarium')]
    public protected(set) Collection $photos;

    #[OneToMany(targetEntity: UserHerbariumRole::class, mappedBy: 'herbarium')]
    public protected(set) Collection $userHerbariumRoles;

    #[OneToMany(targetEntity: Contact::class, mappedBy: 'herbarium')]
    #[OrderBy(['surname' => 'ASC'])]
    public protected(set) Collection $contacts;

    #[ManyToOne(targetEntity: License::class)]
    #[JoinColumn(name: 'license_id', referencedColumnName: 'id', nullable: false)]
    public protected(set) License $license;

    #[Column(type: Types::INTEGER, nullable: false, options: ['default' => 6, 'comment' => 'count of digits in HerbNr stored in JACQ, important for SID prediction and other "standard" representation of the HerbNr'])]
    public protected(set) int $digitsCount = 6;

    #[Column(name: 'grsc_institution', unique: true, nullable: true, options: ['comment' => 'Global Registry of Scientific Collections (GRSciColl) institution ID used for IPT publishing'])]
    public protected(set) ?string $GRSciCollInstitutionID = null;

    #[Column(name: 'grsc_collection', unique: true, nullable: true, options: ['comment' => 'Global Registry of Scientific Collections (GRSciColl) collection ID used for IPT publishing'])]
    public protected(set) ?string $GRSciCollCollectionID = null;

    #[Column(type: Types::INTEGER, nullable: false, options: ['default' => 5, 'comment' => 'minimal filesize[MB] that is accepted during import control'])]
    public protected(set) int $minimalFileSize;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->userHerbariumRoles = new ArrayCollection();
        $this->contacts = new ArrayCollection();
    }

    public function setExternalDatabase(ExternalDatabase $externalDatabase): Herbaria
    {
        $this->externalDatabase = $externalDatabase;

        return $this;
    }

    public function setAcronym(string $acronym): Herbaria
    {
        $this->acronym = $acronym;

        return $this;
    }

    public function setLogo(string $logo): Herbaria
    {
        $this->logo = $logo;

        return $this;
    }

    public function setFullname(string $fullname): Herbaria
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function setAddress(string $address): Herbaria
    {
        $this->address = $address;

        return $this;
    }

    public function setRegexBarcode(string $regexBarcode): Herbaria
    {
        $this->regexBarcode = $regexBarcode;

        return $this;
    }

    public function setRegexFilename(string $regexFilename): Herbaria
    {
        $this->regexFilename = $regexFilename;

        return $this;
    }

    public function setMultipleBarcodeMultiplier(bool $multipleBarcodeMultiplier): Herbaria
    {
        $this->multipleBarcodeMultiplier = $multipleBarcodeMultiplier;

        return $this;
    }

    public function setStrictBarcodeAcronymPrefix(bool $strictBarcodeAcronymPrefix): void
    {
        $this->strictBarcodeAcronymPrefix = $strictBarcodeAcronymPrefix;
    }

    public function setBucket(string $bucket): Herbaria
    {
        $this->bucket = $bucket;

        return $this;
    }

    public function setFallbackFilename(bool $fallbackFilename): Herbaria
    {
        $this->fallbackFilename = $fallbackFilename;

        return $this;
    }

    public function addContact(Contact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
        }

        return $this;
    }

    public function removeContact(Contact $contact): self
    {
        $this->contacts->removeElement($contact);

        return $this;
    }

    public function setLicense(License $license): Herbaria
    {
        $this->license = $license;

        return $this;
    }

    public function setDigitsCount(int $digitsCount): Herbaria
    {
        $this->digitsCount = $digitsCount;

        return $this;
    }

    public function setGRSciCollInstitutionID(?string $GRSciCollInstitutionID): void
    {
        $this->GRSciCollInstitutionID = $GRSciCollInstitutionID;
    }

    public function setGRSciCollCollectionID(?string $GRSciCollCollectionID): void
    {
        $this->GRSciCollCollectionID = $GRSciCollCollectionID;
    }

    public function setMinimalFileSize(int $minimalFileSize): Herbaria
    {
        $this->minimalFileSize = $minimalFileSize;

        return $this;
    }
}
