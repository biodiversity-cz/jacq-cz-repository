<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250908131544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA databots');
        $this->addSql('ALTER TABLE public.databot SET SCHEMA databots;');
        $this->addSql('ALTER TABLE public.databot_results SET SCHEMA databots;');
        $this->addSql('ALTER FUNCTION public.register_databot SET SCHEMA databots;');
        $this->addSql('ALTER TYPE public.enum_databot_result_status SET SCHEMA databots');
        $this->addSql('ALTER TYPE public.enum_databot_role SET SCHEMA databots');
        $this->addSql('
                        CREATE OR REPLACE FUNCTION databots.register_databot(
                    p_name TEXT,
                    p_description TEXT,
                    p_version INTEGER,
                    p_role databots.enum_databot_role
                )
                RETURNS INTEGER
                LANGUAGE plpgsql
                AS $$
                DECLARE
                    existing_bot_id INT;
                    existing_enabled BOOLEAN;
                BEGIN
                    -- Najdi bota podle jména a verze
                    SELECT id, enabled
                    INTO existing_bot_id, existing_enabled
                    FROM databots.databot
                    WHERE name = p_name AND version = p_version;

                    IF FOUND THEN
                        IF existing_enabled THEN
                            -- Verze existuje a je povolená – aktualizuj last_run
                            UPDATE databots.databot
                            SET last_run = NOW(),
                            description = p_description
                            WHERE id = existing_bot_id;

                            RETURN existing_bot_id;
                        ELSE
                            -- Verze existuje, ale je zakázaná – neumožni spuštění
                            RETURN NULL;
                        END IF;
                    ELSE
                        -- Zakázat všechny starší verze se stejným jménem
                        UPDATE databots.databot
                        SET enabled = FALSE
                        WHERE name = p_name AND version < p_version;

                        -- Vložit nového bota
                        INSERT INTO databots.databot (
                            name, description, version, role, enabled,
                            created_at, last_run
                        )
                        VALUES (
                            p_name, p_description, p_version, p_role, TRUE,
                            NOW(), NOW()
                        )

                        RETURNING id INTO existing_bot_id;

                        RETURN existing_bot_id;

                    END IF;
                END;
                $$;
                ');
        $this->addSql('COMMENT ON FUNCTION databots.register_databot(TEXT, TEXT, INTEGER, databots.enum_databot_role)
IS \'Register databot. Return TRUE if a databot is successfully registered and allowed to proceed, otherwise returns FALSE - that mean dependabot should stop and leave.\';');

//        // vytvoření uživatele
//        $this->addSql("CREATE ROLE databot LOGIN PASSWORD 'databots'");

//        GRANT USAGE ON SCHEMA public TO databot;
//        GRANT SELECT ON public.photos TO databot;
//
//        GRANT USAGE ON SCHEMA databots TO databot;
//        GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA databots TO databot;
//        ALTER DEFAULT PRIVILEGES IN SCHEMA databots GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO databot;
//
//        GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA databots TO databot;
//        ALTER DEFAULT PRIVILEGES IN SCHEMA databots GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO databot;
//
//        GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA databots TO databot;
//        ALTER DEFAULT PRIVILEGES IN SCHEMA databots GRANT EXECUTE ON FUNCTIONS TO databot;
//
//        GRANT USAGE ON TYPE databots.enum_databot_role TO databot;
//        GRANT USAGE ON TYPE databots.enum_databot_result_status TO databot;

    }

    public function down(Schema $schema): void
    {
        $this->addSql("REVOKE USAGE ON TYPE databots.enum_databot_role FROM databot");
        $this->addSql("REVOKE USAGE ON TYPE databots.enum_databot_result_status FROM databot");

        // odebrání práv na funkce, sekvence, tabulky
        $this->addSql("REVOKE EXECUTE ON ALL FUNCTIONS IN SCHEMA databots FROM databot");
        $this->addSql("REVOKE ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA databots FROM databot");
        $this->addSql("REVOKE ALL PRIVILEGES ON ALL TABLES IN SCHEMA databots FROM databot");
        $this->addSql("REVOKE CREATE ON SCHEMA databots FROM databot");
        $this->addSql("REVOKE USAGE ON SCHEMA databots FROM databot");

        $this->addSql("REVOKE SELECT ON ALL TABLES IN SCHEMA public FROM databot");
        $this->addSql("REVOKE USAGE ON SCHEMA public FROM databot");

//        // odstranění uživatele
//        $this->addSql("DROP ROLE databot");

        $this->addSql('ALTER TABLE databots.databot SET SCHEMA public;');
        $this->addSql('ALTER TABLE databots.databot_results SET SCHEMA public;');
        $this->addSql('ALTER FUNCTION databots.register_databot SET SCHEMA public;');
        $this->addSql('ALTER TYPE databots.enum_databot_result_status SET SCHEMA public');
        $this->addSql('ALTER TYPE databots.enum_databot_role SET SCHEMA public');
        $this->addSql('DROP SCHEMA databots');

    }
}
