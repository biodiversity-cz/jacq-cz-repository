<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250224081323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE photos_type (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, color VARCHAR(255) DEFAULT \'primary\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BC4E5C85E237E06 ON photos_type (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BC4E5C86DE44026 ON photos_type (description)');
        $this->addSql('COMMENT ON TABLE photos_type IS \'Types of images (like preserved specimen or photo from the field)\'');
        $this->addSql('COMMENT ON COLUMN photos_type.name IS \'name of the type\'');
        $this->addSql('COMMENT ON COLUMN photos_type.description IS \'short description\'');
        $this->addSql('COMMENT ON COLUMN photos_type.color IS \'CSS color class for status visualisation\'');
          $this->addSql('ALTER TABLE photos ADD type_id INT DEFAULT 1 NOT NULL');
        $this->addSql('COMMENT ON COLUMN photos.type_id IS \'Type of the photo\'');
        $this->addSql('ALTER TABLE photos ADD CONSTRAINT FK_876E0D9C54C8C93 FOREIGN KEY (type_id) REFERENCES photos_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_876E0D9C54C8C93 ON photos (type_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos DROP CONSTRAINT FK_876E0D9C54C8C93');
        $this->addSql('DROP TABLE photos_type');
        $this->addSql('DROP INDEX IDX_876E0D9C54C8C93');
        $this->addSql('ALTER TABLE photos DROP type_id');
    }
}
