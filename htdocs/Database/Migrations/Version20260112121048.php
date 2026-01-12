<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260112121048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria ADD digits_count INT DEFAULT 6 NOT NULL');
        $this->addSql('COMMENT ON COLUMN herbaria.digits_count IS \'count of digits in HerbNr stored in JACQ, important for SID prediction and other "standard" representation of the HerbNr\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE herbaria DROP digits_count');
    }
}
