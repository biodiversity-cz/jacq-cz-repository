<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250731095006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photos ADD databot_thumb_filename VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN photos.databot_thumb_filename IS \'Filename of PNG file devoted for Databots\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_876E0D9509FB93F ON photos (databot_thumb_filename)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_876E0D9509FB93F');
        $this->addSql('ALTER TABLE photos DROP databot_thumb_filename');
    }
}
