<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250302115005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE enregistrement CHANGE temps_vers_prochain_passage temps_vers_prochain_passage VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\', CHANGE temps_vers_prochain_passage2 temps_vers_prochain_passage2 VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\'');
        $this->addSql('ALTER TABLE enregistrement_group ADD ligne_id INT NOT NULL');
        $this->addSql('ALTER TABLE enregistrement_group ADD CONSTRAINT FK_F389F5EF5A438E76 FOREIGN KEY (ligne_id) REFERENCES ligne (id)');
        $this->addSql('CREATE INDEX IDX_F389F5EF5A438E76 ON enregistrement_group (ligne_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE enregistrement CHANGE temps_vers_prochain_passage temps_vers_prochain_passage VARCHAR(255) DEFAULT \'NULL\' COMMENT \'(DC2Type:dateinterval)\', CHANGE temps_vers_prochain_passage2 temps_vers_prochain_passage2 VARCHAR(255) DEFAULT \'NULL\' COMMENT \'(DC2Type:dateinterval)\'');
        $this->addSql('ALTER TABLE enregistrement_group DROP FOREIGN KEY FK_F389F5EF5A438E76');
        $this->addSql('DROP INDEX IDX_F389F5EF5A438E76 ON enregistrement_group');
        $this->addSql('ALTER TABLE enregistrement_group DROP ligne_id');
    }
}
