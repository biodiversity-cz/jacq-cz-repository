<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106075030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos ADD bucket_suffix VARCHAR(255) NOT NULL DEFAULT \'-01\'');
        $this->addSql('ALTER TABLE photos ALTER bucket_suffix DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN photos.bucket_suffix IS \'Suffix determining bucket set where the related files are stored\'');
        $this->addSql('COMMENT ON COLUMN cetaf.sid.stable_uri IS \'stable URI assigned to the specimen\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('COMMENT ON COLUMN cetaf.sid.stable_uri IS \'URI assigned via ark.biodiversity.cz\'');
        $this->addSql('ALTER TABLE photos DROP bucket_suffix');
    }
}
