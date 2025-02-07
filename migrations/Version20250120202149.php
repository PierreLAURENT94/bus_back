<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250120202149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE arret (id INT AUTO_INCREMENT NOT NULL, nom_id VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE arret_ligne (arret_id INT NOT NULL, ligne_id INT NOT NULL, INDEX IDX_3A128AB968F1C150 (arret_id), INDEX IDX_3A128AB95A438E76 (ligne_id), PRIMARY KEY(arret_id, ligne_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE arret_ligne ADD CONSTRAINT FK_3A128AB968F1C150 FOREIGN KEY (arret_id) REFERENCES arret (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE arret_ligne ADD CONSTRAINT FK_3A128AB95A438E76 FOREIGN KEY (ligne_id) REFERENCES ligne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ligne CHANGE logo logo VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arret_ligne DROP FOREIGN KEY FK_3A128AB968F1C150');
        $this->addSql('ALTER TABLE arret_ligne DROP FOREIGN KEY FK_3A128AB95A438E76');
        $this->addSql('DROP TABLE arret');
        $this->addSql('DROP TABLE arret_ligne');
        $this->addSql('ALTER TABLE ligne CHANGE logo logo VARCHAR(255) DEFAULT \'NULL\'');
    }
}
