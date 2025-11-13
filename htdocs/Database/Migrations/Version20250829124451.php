<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250829124451 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE herbaria ADD multiple_barcode_multiplier BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('COMMENT ON COLUMN herbaria.multiple_barcode_multiplier IS \'When multiple valid barcodes are present, multiply image to all these IDs\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE herbaria DROP multiple_barcode_multiplier');
    }
}
