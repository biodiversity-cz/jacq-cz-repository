<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251222080927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cetaf.sid ADD previous_identifications VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX uniq_265fb4dd41405e39');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cetaf.sid DROP previous_identifications');
        $this->addSql('CREATE UNIQUE INDEX uniq_265fb4dd41405e39 ON external_database (element)');
    }
}
