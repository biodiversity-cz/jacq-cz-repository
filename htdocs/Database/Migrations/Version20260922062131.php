<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260922062131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria RENAME COLUMN always_trailing_zeros TO digits_count_on_substring');
        $this->addSql('COMMENT ON COLUMN herbaria.digits_count_on_substring IS \'Digits count is applied on a substring of the specimenId\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria RENAME COLUMN digits_count_on_substring TO always_trailing_zeros');
        $this->addSql('COMMENT ON COLUMN herbaria.always_trailing_zeros IS \'Add trailing zeros even in case of no-strictly-digits herbNr\'');
    }
}
