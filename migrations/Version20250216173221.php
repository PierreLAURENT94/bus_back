<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216173221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne ADD initialisee TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE ligne_arret ADD index_direction1 INT DEFAULT NULL, ADD index_direction2 INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne DROP initialisee');
        $this->addSql('ALTER TABLE ligne_arret DROP index_direction1, DROP index_direction2');
    }
}
