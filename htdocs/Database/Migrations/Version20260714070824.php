<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260714070824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria ADD minimal_file_size INT DEFAULT 5 NOT NULL');
        $this->addSql('COMMENT ON COLUMN herbaria.minimal_file_size IS \'minimal filesize[MB] that is accepted during import control\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria DROP minimal_file_size');
    }
}
