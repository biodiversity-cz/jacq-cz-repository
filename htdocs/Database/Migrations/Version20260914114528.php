<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914114528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria ADD always_trailing_zeros BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('COMMENT ON COLUMN herbaria.always_trailing_zeros IS \'Add trailing zeros even in case of no-strictly-digits herbNr\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria DROP always_trailing_zeros');
    }
}
