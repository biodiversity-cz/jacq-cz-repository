<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * add ROR for herbaria.
 */
final class Version20261015061250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria ADD ror VARCHAR(255) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN herbaria.ror IS \'Research Organization Registry (ROR)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria DROP ror');
    }
}
