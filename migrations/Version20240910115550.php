<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910115550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant MODIFY id INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON participant');
        $this->addSql('ALTER TABLE participant DROP id, CHANGE id_participant id_participant INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE participant ADD PRIMARY KEY (id_participant)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant ADD id INT AUTO_INCREMENT NOT NULL, CHANGE id_participant id_participant INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
