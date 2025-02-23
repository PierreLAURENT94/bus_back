<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223183339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE enregistrement ADD ligne_arret_direction1_id INT DEFAULT NULL, ADD ligne_arret_direction2_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE enregistrement ADD CONSTRAINT FK_15FA02FC827BE2D FOREIGN KEY (ligne_arret_direction1_id) REFERENCES ligne_arret (id)');
        $this->addSql('ALTER TABLE enregistrement ADD CONSTRAINT FK_15FA02FDA9211C3 FOREIGN KEY (ligne_arret_direction2_id) REFERENCES ligne_arret (id)');
        $this->addSql('CREATE INDEX IDX_15FA02FC827BE2D ON enregistrement (ligne_arret_direction1_id)');
        $this->addSql('CREATE INDEX IDX_15FA02FDA9211C3 ON enregistrement (ligne_arret_direction2_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE enregistrement DROP FOREIGN KEY FK_15FA02FC827BE2D');
        $this->addSql('ALTER TABLE enregistrement DROP FOREIGN KEY FK_15FA02FDA9211C3');
        $this->addSql('DROP INDEX IDX_15FA02FC827BE2D ON enregistrement');
        $this->addSql('DROP INDEX IDX_15FA02FDA9211C3 ON enregistrement');
        $this->addSql('ALTER TABLE enregistrement DROP ligne_arret_direction1_id, DROP ligne_arret_direction2_id');
    }
}
