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
#[Table(name: 'import_multiplier', options: ['comment' => 'Holds remaining valid IDs of mixed specimens (with multiple barcodes) to be cloned during the import process'])]
// phpcs:disable SlevomatCodingStandard.Classes.SuperfluousErrorNaming.SuperfluousSuffix
class ImportMultiplier
{

// phpcs:enable
    use TId;

    #[OneToOne(targetEntity: Photos::class)]
    #[JoinColumn(name: 'photo_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE', options: ['comment' => 'photo to which this multiplier belongs'])]
    protected Photos $photo;

    #[Column(type: "json")]
    private array $barcodes = [];


    public function getPhoto(): Photos
    {
        return $this->photo;
    }

    public function setPhoto(Photos $photo): ImportMultiplier
    {
        $this->photo = $photo;

        return $this;
    }

    public function getBarcodes(): array
    {
        return $this->barcodes;
    }

    public function setBarcodes(array $barcodes): self
    {
        $this->barcodes = $barcodes;
        return $this;
    }

    public function addBarcode(string $barcode): self
    {
        if (!in_array($barcode, $this->barcodes, true)) {
            $this->barcodes[] = $barcode;
        }
        return $this;
    }

    public function removeBarcode(string $barcode): self
    {
        $this->barcodes = array_filter($this->barcodes, fn($t) => $t !== $barcode);
        return $this;
    }


}
