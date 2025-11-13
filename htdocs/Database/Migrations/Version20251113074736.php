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
        $this->addSql('ALTER TABLE photos ADD pid TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE photos ADD specimen_pid TEXT DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN photos.pid IS \'Persistent ID of photo\'');
        $this->addSql('COMMENT ON COLUMN photos.specimen_pid IS \'Persistent ID of external specimen entity to which this photo belongs\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_876E0D95550C4ED ON photos (pid)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_876E0D95550C4ED');
        $this->addSql('ALTER TABLE photos DROP pid');
        $this->addSql('ALTER TABLE photos DROP specimen_pid');
        $this->addSql('DROP INDEX UNIQ_190AE20B3052E14B');
        $this->addSql('ALTER TABLE photos_status DROP succession');
    }
}
