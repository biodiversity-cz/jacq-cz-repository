<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250909112650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE databots.databot ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER INDEX databots.uniq_50ab601d5e237e06bf1cd3c3 RENAME TO UNIQ_CA5AAE475E237E06BF1CD3C3');
        $this->addSql('ALTER TABLE databots.databot_results ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER INDEX databots.idx_cc2b43905646e484 RENAME TO IDX_D197BD455646E484');
        $this->addSql('ALTER INDEX databots.idx_cc2b43907e9e4c8c RENAME TO IDX_D197BD457E9E4C8C');
        $this->addSql('ALTER INDEX databots.uniq_cc2b43905646e4847e9e4c8c RENAME TO UNIQ_D197BD455646E4847E9E4C8C');
        $this->addSql('ALTER TABLE photos ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE users ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE databots.databot ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER INDEX databots.uniq_ca5aae475e237e06bf1cd3c3 RENAME TO uniq_50ab601d5e237e06bf1cd3c3');
        $this->addSql('ALTER TABLE databots.databot_results ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER INDEX databots.idx_d197bd457e9e4c8c RENAME TO idx_cc2b43907e9e4c8c');
        $this->addSql('ALTER INDEX databots.idx_d197bd455646e484 RENAME TO idx_cc2b43905646e484');
        $this->addSql('ALTER INDEX databots.uniq_d197bd455646e4847e9e4c8c RENAME TO uniq_cc2b43905646e4847e9e4c8c');
        $this->addSql('ALTER TABLE photos ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER created_at DROP DEFAULT');
    }
}
