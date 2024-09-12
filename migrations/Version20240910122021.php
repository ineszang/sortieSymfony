<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910122021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etat DROP id_etat');
        $this->addSql('ALTER TABLE lieu DROP id_lieu');
        $this->addSql('ALTER TABLE participant DROP id_participant');
        $this->addSql('ALTER TABLE site DROP id_site');
        $this->addSql('ALTER TABLE sortie DROP id_sortie');
        $this->addSql('ALTER TABLE ville DROP id_ville');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lieu ADD id_lieu INT NOT NULL');
        $this->addSql('ALTER TABLE etat ADD id_etat INT NOT NULL');
        $this->addSql('ALTER TABLE sortie ADD id_sortie INT NOT NULL');
        $this->addSql('ALTER TABLE participant ADD id_participant INT NOT NULL');
        $this->addSql('ALTER TABLE site ADD id_site INT NOT NULL');
        $this->addSql('ALTER TABLE ville ADD id_ville INT NOT NULL');
    }
}
