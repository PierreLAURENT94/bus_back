<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223183059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE mig');
        $this->addSql('DROP TABLE stop_time');
        $this->addSql('DROP TABLE trip');
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE enregistrement DROP FOREIGN KEY FK_15FA02F376BD066');
        $this->addSql('DROP INDEX IDX_15FA02F376BD066 ON enregistrement');
        $this->addSql('ALTER TABLE enregistrement DROP ligne_arret_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mig (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE stop_time (id INT AUTO_INCREMENT NOT NULL, trip_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, stop_sequence INT NOT NULL, stop_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE trip (id INT AUTO_INCREMENT NOT NULL, route_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, trip_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE enregistrement ADD ligne_arret_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enregistrement ADD CONSTRAINT FK_15FA02F376BD066 FOREIGN KEY (ligne_arret_id) REFERENCES ligne_arret (id)');
        $this->addSql('CREATE INDEX IDX_15FA02F376BD066 ON enregistrement (ligne_arret_id)');
    }
}
