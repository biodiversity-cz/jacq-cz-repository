<?php declare(strict_types=1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TCreatedAt;
use App\Model\Database\Entity\Attributes\TId;
use App\Model\Database\Enums\DatabotResultStatus;
use App\Model\Database\Enums\EnumDatabotStatusType;
use App\Model\Database\Repository\DatabotResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DatabotResultRepository::class)]
#[ORM\Table(name: 'databot_results', schema: 'databots', options: ['comment' => 'Results of Databot runs per photo'])]
#[ORM\UniqueConstraint(columns: ['databot_id', 'photo_id'])]
class DatabotResult
{
    use TId;
    use TCreatedAt;

    #[ORM\ManyToOne(targetEntity: Databot::class)]
    #[ORM\JoinColumn(name: 'databot_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected(set) Databot $databot;

    #[ORM\ManyToOne(targetEntity: Photos::class, inversedBy: 'databotResults')]
    #[ORM\JoinColumn(name: 'photo_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected(set) Photos $photo;

    #[ORM\Column(
        type: 'string',
        nullable: false,
        enumType: DatabotResultStatus::class,
        options: ['comment' => 'Result status: ok, error, warning...', 'default' => DatabotResultStatus::OK])]
    protected(set) DatabotResultStatus $status = DatabotResultStatus::OK;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'Optional log or error description'])]
    protected(set) ?string $message = null;

    /** @var ?mixed[] */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true, 'comment' => 'Structured result data, e.g. metrics, checks'])]
    protected(set) ?array $resultData = null;

    public function setDatabot(Databot $databot): DatabotResult
    {
        $this->databot = $databot;
        return $this;
    }

    public function setPhoto(Photos $photo): DatabotResult
    {
        $this->photo = $photo;
        return $this;
    }

    public function setStatus(DatabotResultStatus $status): DatabotResult
    {
        $this->status = $status;
        return $this;
    }

    public function setMessage(?string $message): DatabotResult
    {
        $this->message = $message;
        return $this;
    }

    public function setResultData(?array $resultData): DatabotResult
    {
        $this->resultData = $resultData;
        return $this;
    }

}
