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
        $this->addSql('UPDATE photos_status SET  color = \'warning\' WHERE id = 1');
        $this->addSql('UPDATE photos_status SET  color = \'danger\' WHERE id = 2');
        $this->addSql('UPDATE photos_status SET  color = \'primary\' WHERE id = 3');
        $this->addSql('UPDATE photos_status SET  color = \'success\' WHERE id = 4');
        $this->addSql('UPDATE photos_status SET  color = \'secondary\' WHERE id = 5');
        $this->addSql('COMMENT ON COLUMN photos_status.color IS \'CSS color class for status visualisation\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_190AE20B665648E9 ON photos_status (color)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_190AE20B665648E9');
        $this->addSql('ALTER TABLE photos_status DROP color');
    }
}
