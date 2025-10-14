<?php declare(strict_types = 1);

namespace App\Model\Database\Entity;

use App\Model\Database\Entity\Attributes\TId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;

#[Entity()]
#[Table(name: 'excluded_download_log', schema: 'front', options: ['comment' => 'IP addresses excluded from download logging'])]
class ExcludedDownloadLog
{

    use TId;

    #[Column(type: Types::STRING, nullable: false, options: ['comment' => 'IP address to be excluded from logging'])]
    protected string $ip;

    #[Column(type: Types::STRING, nullable: false, options: ['comment' => 'Description of why this IP is excluded'])]
    protected string $description;

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): ExcludedDownloadLog
    {
        $this->ip = $ip;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): ExcludedDownloadLog
    {
        $this->description = $description;
        return $this;
    }
}