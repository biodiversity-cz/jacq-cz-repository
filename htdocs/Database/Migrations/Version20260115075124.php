<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260115075124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX uniq_herbarium_externalid ON cetaf.sid (herbarium, external_id_from_institution)');
        $this->addSql('ALTER TABLE cetaf.sid DROP verbatim_identification');
        $this->addSql('DROP INDEX cetaf.uniq_3eabd69195047906');
        $this->addSql('ALTER TABLE cetaf.sid DROP stable_uri');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cetaf.sid ADD stable_uri VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN cetaf.sid.stable_uri IS \'stable URI assigned to the specimen\'');
        $this->addSql('CREATE UNIQUE INDEX uniq_3eabd69195047906 ON cetaf.sid (stable_uri)');
        $this->addSql('DROP INDEX cetaf.uniq_herbarium_externalid');
        $this->addSql('ALTER TABLE cetaf.sid ADD verbatim_identification VARCHAR(255) DEFAULT NULL');
    }
}
