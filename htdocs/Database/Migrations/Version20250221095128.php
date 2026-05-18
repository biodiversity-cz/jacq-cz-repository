<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250221095128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photos_status ADD color VARCHAR(255) DEFAULT \'primary\' NOT NULL');
        $this->addSql('COMMENT ON COLUMN photos_status.color IS \'CSS color class for status visualisation\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_190AE20B665648E9 ON photos_status (color)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_190AE20B665648E9');
        $this->addSql('ALTER TABLE photos_status DROP color');
    }
}
