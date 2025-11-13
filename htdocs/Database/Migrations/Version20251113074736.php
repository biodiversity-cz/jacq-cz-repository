<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113074736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('COMMENT ON COLUMN funding.ccmm_format IS \'Structured XML data for OAI-PMH CCMM export\'');
        $this->addSql('ALTER TABLE photos_status ADD succession INT DEFAULT NULL');
        $this->addSql('UPDATE photos_status SET succession = id WHERE succession IS NULL');
        $this->addSql('ALTER TABLE photos_status ALTER COLUMN succession SET NOT NULL, ALTER COLUMN succession DROP DEFAULT');
        $this->addSql('DROP INDEX uniq_190ae20b665648e9');
        $this->addSql('COMMENT ON COLUMN photos_status.succession IS \'how to order statuses when presented, not necessary the only succession that exists\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_190AE20B3052E14B ON photos_status (succession)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_190AE20B3052E14B');
        $this->addSql('ALTER TABLE photos_status DROP succession');
    }
}
