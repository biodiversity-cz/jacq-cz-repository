<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118125704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos ADD embargo_timeout TIMESTAMP(0) WITHOUT TIME ZONE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos DROP embargo_timeout');
    }
}
