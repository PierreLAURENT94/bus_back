<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302103122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE arret (id INT AUTO_INCREMENT NOT NULL, nom_id VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, ville VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enregistrement (id INT AUTO_INCREMENT NOT NULL, ligne_arret_direction1_id INT DEFAULT NULL, ligne_arret_direction2_id INT DEFAULT NULL, enregistrement_group_id INT DEFAULT NULL, temps_vers_prochain_passage VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', temps_vers_prochain_passage2 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', INDEX IDX_15FA02FC827BE2D (ligne_arret_direction1_id), INDEX IDX_15FA02FDA9211C3 (ligne_arret_direction2_id), INDEX IDX_15FA02F39507C22 (enregistrement_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE enregistrement_group (id INT AUTO_INCREMENT NOT NULL, heure DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne (id INT AUTO_INCREMENT NOT NULL, nom_id VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, couleur_hexa VARCHAR(6) NOT NULL, texte_couleur_hexa VARCHAR(6) NOT NULL, initialisee TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne_arret (id INT AUTO_INCREMENT NOT NULL, ligne_id INT DEFAULT NULL, arret_id INT DEFAULT NULL, ordre INT NOT NULL, index_direction1 INT DEFAULT NULL, index_direction2 INT DEFAULT NULL, INDEX IDX_B87DBD3E5A438E76 (ligne_id), INDEX IDX_B87DBD3E68F1C150 (arret_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE enregistrement ADD CONSTRAINT FK_15FA02FC827BE2D FOREIGN KEY (ligne_arret_direction1_id) REFERENCES ligne_arret (id)');
        $this->addSql('ALTER TABLE enregistrement ADD CONSTRAINT FK_15FA02FDA9211C3 FOREIGN KEY (ligne_arret_direction2_id) REFERENCES ligne_arret (id)');
        $this->addSql('ALTER TABLE enregistrement ADD CONSTRAINT FK_15FA02F39507C22 FOREIGN KEY (enregistrement_group_id) REFERENCES enregistrement_group (id)');
        $this->addSql('ALTER TABLE ligne_arret ADD CONSTRAINT FK_B87DBD3E5A438E76 FOREIGN KEY (ligne_id) REFERENCES ligne (id)');
        $this->addSql('ALTER TABLE ligne_arret ADD CONSTRAINT FK_B87DBD3E68F1C150 FOREIGN KEY (arret_id) REFERENCES arret (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE enregistrement DROP FOREIGN KEY FK_15FA02FC827BE2D');
        $this->addSql('ALTER TABLE enregistrement DROP FOREIGN KEY FK_15FA02FDA9211C3');
        $this->addSql('ALTER TABLE enregistrement DROP FOREIGN KEY FK_15FA02F39507C22');
        $this->addSql('ALTER TABLE ligne_arret DROP FOREIGN KEY FK_B87DBD3E5A438E76');
        $this->addSql('ALTER TABLE ligne_arret DROP FOREIGN KEY FK_B87DBD3E68F1C150');
        $this->addSql('DROP TABLE arret');
        $this->addSql('DROP TABLE enregistrement');
        $this->addSql('DROP TABLE enregistrement_group');
        $this->addSql('DROP TABLE ligne');
        $this->addSql('DROP TABLE ligne_arret');
    }
}
