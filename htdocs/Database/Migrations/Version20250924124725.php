<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250924124725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_herbaria (user_id INT NOT NULL, herbaria_id INT NOT NULL, PRIMARY KEY (user_id, herbaria_id))');
        $this->addSql('CREATE INDEX IDX_A052C6BAA76ED395 ON user_herbaria (user_id)');
        $this->addSql('CREATE INDEX IDX_A052C6BA1F5637D3 ON user_herbaria (herbaria_id)');
        $this->addSql('ALTER TABLE user_herbaria ADD CONSTRAINT FK_A052C6BAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_herbaria ADD CONSTRAINT FK_A052C6BA1F5637D3 FOREIGN KEY (herbaria_id) REFERENCES herbaria (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT fk_1483a5e9dd127992');
        $this->addSql('DROP INDEX idx_1483a5e9dd127992');
        $this->addSql('ALTER TABLE users ADD openid_subject TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD openid_provider TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD openid_id_token TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD openid_refresh_token TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD last_visited_herbarium INT DEFAULT NULL');
        $this->addSql('UPDATE users SET last_visited_herbarium = herbarium_id');
        $this->addSql('ALTER TABLE users DROP herbarium_id');
        $this->addSql('COMMENT ON COLUMN users.openid_subject IS \'OpenID subject identifier\'');
        $this->addSql('COMMENT ON COLUMN users.openid_provider IS \'OpenID provider\'');
        $this->addSql('COMMENT ON COLUMN users.openid_id_token IS \'OpenID ID token\'');
        $this->addSql('COMMENT ON COLUMN users.openid_refresh_token IS \'OpenID refresh token\'');
        $this->addSql('COMMENT ON COLUMN users.last_visited_herbarium IS \'Last visited herbarium\'');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E91299D7AD FOREIGN KEY (last_visited_herbarium) REFERENCES herbaria (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_1483A5E91299D7AD ON users (last_visited_herbarium)');
        $this->addSql('INSERT INTO user_herbaria (user_id, herbaria_id) SELECT id, last_visited_herbarium FROM users');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_herbaria DROP CONSTRAINT FK_A052C6BAA76ED395');
        $this->addSql('ALTER TABLE user_herbaria DROP CONSTRAINT FK_A052C6BA1F5637D3');
        $this->addSql('DROP TABLE user_herbaria');
        $this->addSql('ALTER TABLE users DROP CONSTRAINT FK_1483A5E91299D7AD');
        $this->addSql('DROP INDEX IDX_1483A5E91299D7AD');
        $this->addSql('ALTER TABLE users ADD herbarium_id INT NOT NULL');
        $this->addSql('UPDATE users SET herbarium_id = last_visited_herbarium');
        $this->addSql('ALTER TABLE users DROP openid_subject');
        $this->addSql('ALTER TABLE users DROP openid_provider');
        $this->addSql('ALTER TABLE users DROP openid_id_token');
        $this->addSql('ALTER TABLE users DROP openid_refresh_token');
        $this->addSql('ALTER TABLE users DROP last_visited_herbarium');
        $this->addSql('COMMENT ON COLUMN users.herbarium_id IS \'Herbarium\'');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT fk_1483a5e9dd127992 FOREIGN KEY (herbarium_id) REFERENCES herbaria (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1483a5e9dd127992 ON users (herbarium_id)');
    }
}
