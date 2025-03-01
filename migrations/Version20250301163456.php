<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301163456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE enregistrement DROP prochain_passage, CHANGE temps_vers_prochain_passage temps_vers_prochain_passage VARCHAR(255) DEFAULT NULL COMMENT \'(DC2Type:dateinterval)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE arret CHANGE ville ville VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE enregistrement ADD prochain_passage DATETIME NOT NULL, CHANGE temps_vers_prochain_passage temps_vers_prochain_passage VARCHAR(255) DEFAULT \'NULL\' COMMENT \'(DC2Type:dateinterval)\'');
    }
}
