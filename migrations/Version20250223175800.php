<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223175800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE enregistrement (id INT AUTO_INCREMENT NOT NULL, ligne_arret_id INT DEFAULT NULL, date_time DATETIME NOT NULL, prochain_passage DATETIME NOT NULL, INDEX IDX_15FA02F376BD066 (ligne_arret_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE enregistrement ADD CONSTRAINT FK_15FA02F376BD066 FOREIGN KEY (ligne_arret_id) REFERENCES ligne_arret (id)');
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE enregistrement DROP FOREIGN KEY FK_15FA02F376BD066');
        $this->addSql('DROP TABLE enregistrement');
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\'');
    }
}
