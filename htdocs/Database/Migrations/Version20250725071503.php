<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250725071503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'minor integrity fixes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER INDEX uniq_50ab601d5e237e06 RENAME TO UNIQ_50AB601D5E237E06BF1CD3C3');
        $this->addSql('ALTER TABLE photos ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE photos ALTER original_file_timestamp TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN photos.created_at IS \'\'');
        $this->addSql('COMMENT ON COLUMN photos.original_file_timestamp IS \'Timestamp of original file creation\'');
        $this->addSql('ALTER TABLE users ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN users.created_at IS \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER INDEX uniq_50ab601d5e237e06bf1cd3c3 RENAME TO uniq_50ab601d5e237e06');
        $this->addSql('ALTER TABLE photos ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE photos ALTER original_file_timestamp TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN photos.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN photos.original_file_timestamp IS \'Timestamp of original file creation(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE users ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN users.created_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
