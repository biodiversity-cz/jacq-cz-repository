<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250206084851 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE herbaria ADD regex_barcode VARCHAR(255) NOT NULL default \'\'');
        $this->addSql('ALTER TABLE herbaria ADD regex_filename VARCHAR(255) NOT NULL default \'\'');
        $this->addSql('COMMENT ON COLUMN herbaria.regex_barcode IS \'RegEx for barcode on the specimen\'');
        $this->addSql('COMMENT ON COLUMN herbaria.regex_filename IS \'RegEx for image filenames\'');
        $this->addSql('ALTER TABLE photos ADD use_barcode BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE photos ALTER original_file_timestamp TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE photos ALTER lastedit_timestamp SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('COMMENT ON COLUMN photos.use_barcode IS \'If true, the import pipeline will use the barcode detection. If false, the filename will be used to detect specimen id\'');
        $this->addSql('COMMENT ON COLUMN photos.message IS \'Result of import into the repository\'');
        $this->addSql('COMMENT ON COLUMN photos.original_file_timestamp IS \'Timestamp of original file creation(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN photos.exif IS \'raw EXIF data extracted from Archive Master file\'');
        $this->addSql('COMMENT ON COLUMN photos.identify IS \'Imagick -verbose identify metadata output from the Archive Master file\'');
        $this->addSql('ALTER TABLE users ALTER lastedit_timestamp SET DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE herbaria DROP regex_barcode');
        $this->addSql('ALTER TABLE herbaria DROP regex_filename');
        $this->addSql('ALTER TABLE users ALTER lastedit_timestamp DROP DEFAULT');
        $this->addSql('ALTER TABLE photos DROP use_barcode');
        $this->addSql('ALTER TABLE photos ALTER lastedit_timestamp SET DEFAULT \'2024-09-09 08:04:15.480519\'');
        $this->addSql('ALTER TABLE photos ALTER original_file_timestamp TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN photos.message IS \'Result of migration\'');
        $this->addSql('COMMENT ON COLUMN photos.exif IS \'Imagick -verbose identify metadata output from the Archive Master file\'');
        $this->addSql('COMMENT ON COLUMN photos.identify IS NULL');
        $this->addSql('COMMENT ON COLUMN photos.original_file_timestamp IS NULL');
    }
}
