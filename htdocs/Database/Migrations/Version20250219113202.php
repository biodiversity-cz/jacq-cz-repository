<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219113202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE photos_error DROP CONSTRAINT FK_8A47742D7E9E4C8C');
        $this->addSql('ALTER TABLE photos_error ADD CONSTRAINT FK_8A47742D7E9E4C8C FOREIGN KEY (photo_id) REFERENCES photos (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE photos_error DROP CONSTRAINT fk_8a47742d7e9e4c8c');
        $this->addSql('ALTER TABLE photos_error ADD CONSTRAINT fk_8a47742d7e9e4c8c FOREIGN KEY (photo_id) REFERENCES photos (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
