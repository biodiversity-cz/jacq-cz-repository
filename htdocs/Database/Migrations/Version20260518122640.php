<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260518122640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria ADD grsc_institution VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE herbaria ADD grsc_collection VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN herbaria.grsc_institution IS \'Global Registry of Scientific Collections (GRSciColl) institution ID used for IPT publishing\'');
        $this->addSql('COMMENT ON COLUMN herbaria.grsc_collection IS \'Global Registry of Scientific Collections (GRSciColl) collection ID used for IPT publishing\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40DF22BABC1999E0 ON herbaria (grsc_institution)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_40DF22BAA99EB4E2 ON herbaria (grsc_collection)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_40DF22BABC1999E0');
        $this->addSql('DROP INDEX UNIQ_40DF22BAA99EB4E2');
        $this->addSql('ALTER TABLE herbaria DROP grsc_institution');
        $this->addSql('ALTER TABLE herbaria DROP grsc_collection');
    }
}
