<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260430065242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
           $this->addSql('ALTER TABLE herbaria ADD strict_barcode_acronym_prefix BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('COMMENT ON COLUMN herbaria.strict_barcode_acronym_prefix IS \'Require herbarium acronym on the start of the barcode to be accepted as valid\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria DROP strict_barcode_acronym_prefix');
    }
}
