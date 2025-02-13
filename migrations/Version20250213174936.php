<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213174936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE arret (id INT AUTO_INCREMENT NOT NULL, nom_id VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne (id INT AUTO_INCREMENT NOT NULL, nom_id VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, couleur_hexa VARCHAR(6) NOT NULL, texte_couleur_hexa VARCHAR(6) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne_arret (id INT AUTO_INCREMENT NOT NULL, ligne_id INT DEFAULT NULL, arret_id INT DEFAULT NULL, ordre INT NOT NULL, INDEX IDX_B87DBD3E5A438E76 (ligne_id), INDEX IDX_B87DBD3E68F1C150 (arret_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ligne_arret ADD CONSTRAINT FK_B87DBD3E5A438E76 FOREIGN KEY (ligne_id) REFERENCES ligne (id)');
        $this->addSql('ALTER TABLE ligne_arret ADD CONSTRAINT FK_B87DBD3E68F1C150 FOREIGN KEY (arret_id) REFERENCES arret (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_arret DROP FOREIGN KEY FK_B87DBD3E5A438E76');
        $this->addSql('ALTER TABLE ligne_arret DROP FOREIGN KEY FK_B87DBD3E68F1C150');
        $this->addSql('DROP TABLE arret');
        $this->addSql('DROP TABLE ligne');
        $this->addSql('DROP TABLE ligne_arret');
    }
}
