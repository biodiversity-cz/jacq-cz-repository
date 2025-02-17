<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217125735 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE herbaria ADD fallback_filename BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE herbaria ALTER regex_barcode DROP DEFAULT');
        $this->addSql('ALTER TABLE herbaria ALTER regex_filename DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN herbaria.fallback_filename IS \'Allow use filenam when barcode is not present in the image\'');
        $this->addSql('ALTER TABLE photos DROP use_barcode');
        $this->addSql('COMMENT ON COLUMN photos.original_filename IS \'Filename that was provided during curator upload, could make sense or completely missing semantic content\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE photos ADD use_barcode BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('COMMENT ON COLUMN photos.use_barcode IS \'If true, the import pipeline will use the barcode detection. If false, the filename will be used to detect specimen id\'');
        $this->addSql('COMMENT ON COLUMN photos.original_filename IS \'Filename that was provided during curator upload, could make sense or completely missing semantical content\'');
        $this->addSql('ALTER TABLE herbaria DROP fallback_filename');
        $this->addSql('ALTER TABLE herbaria ALTER regex_barcode SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE herbaria ALTER regex_filename SET DEFAULT \'\'');
    }
}
