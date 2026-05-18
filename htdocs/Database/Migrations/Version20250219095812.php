<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219095812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE import_error (id SERIAL NOT NULL, photo_id INT NOT NULL, duplicate_id INT DEFAULT NULL, thumbnail BYTEA DEFAULT NULL, message TEXT NOT NULL, barcodes TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8A47742D7E9E4C8C ON import_error (photo_id)');
        $this->addSql('COMMENT ON TABLE import_error IS \'Errors that occur during the import\'');
        $this->addSql('COMMENT ON COLUMN import_error.photo_id IS \'photo to which this error belongs\'');
        $this->addSql('COMMENT ON COLUMN import_error.duplicate_id IS \'already imported photo to which is this probably duplicity\'');
        $this->addSql('COMMENT ON COLUMN import_error.thumbnail IS \'Thumbnail during import phase\'');
        $this->addSql('COMMENT ON COLUMN import_error.message IS \'description fo the error\'');
        $this->addSql('COMMENT ON COLUMN import_error.barcodes IS \'barcode detected in the image\'');
        $this->addSql('ALTER TABLE import_error ADD CONSTRAINT FK_8A47742D7E9E4C8C FOREIGN KEY (photo_id) REFERENCES photos (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE import_error ADD CONSTRAINT FK_8A47742DBC12F48A FOREIGN KEY (duplicate_id) REFERENCES photos (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE photos DROP message');
        $this->addSql('ALTER TABLE photos DROP thumbnail');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE import_error DROP CONSTRAINT FK_8A47742D7E9E4C8C');
        $this->addSql('DROP TABLE import_error');
        $this->addSql('ALTER TABLE photos ADD message TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE photos ADD thumbnail BYTEA DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN photos.message IS \'Result of import into the repository\'');
        $this->addSql('COMMENT ON COLUMN photos.thumbnail IS \'Thumbnail during import phase\'');
    }
}
