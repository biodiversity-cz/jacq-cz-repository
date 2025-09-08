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

//        // vytvoření uživatele
//        $this->addSql("CREATE ROLE databot LOGIN PASSWORD 'databots'");
//
//        // přístup do public (jen čtení)
//        $this->addSql("GRANT USAGE ON SCHEMA public TO databot");
//        $this->addSql("GRANT SELECT ON ALL TABLES IN SCHEMA public TO databot");
//        $this->addSql("ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO databot");
//
//        // schéma databots (plná práva)
//        $this->addSql("GRANT USAGE ON SCHEMA databots TO databot");
//        $this->addSql("GRANT CREATE ON SCHEMA databots TO databot");
//
//        $this->addSql("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA databots TO databot");
//        $this->addSql("ALTER DEFAULT PRIVILEGES IN SCHEMA databots GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO databot");
//
//        $this->addSql("GRANT USAGE, SELECT, UPDATE ON ALL SEQUENCES IN SCHEMA databots TO databot");
//        $this->addSql("ALTER DEFAULT PRIVILEGES IN SCHEMA databots GRANT USAGE, SELECT, UPDATE ON SEQUENCES TO databot");
//
//        $this->addSql("GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA databots TO databot");
//        $this->addSql("ALTER DEFAULT PRIVILEGES IN SCHEMA databots GRANT EXECUTE ON FUNCTIONS TO databot");
//
//        // přidání práv na typy (enumy atd.)
//        $this->addSql("GRANT USAGE ON TYPE databots.enum_databot_role TO databot");
//        $this->addSql("GRANT USAGE ON TYPE databots.enum_databot_result_status TO databot");


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
