<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260114082828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos ADD archive_file_checksum VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN photos.archive_file_checksum IS \'MD5 hash of master archive file\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos DROP archive_file_checksum');
    }
}
