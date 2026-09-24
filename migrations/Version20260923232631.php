<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260923232631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables categorie et ressource';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_497DD634A4D60759 (libelle), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ressource (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, date_ajout DATETIME NOT NULL, categorie_id INT NOT NULL, INDEX IDX_939F4544BCF5E72D (categorie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE ressource ADD CONSTRAINT FK_939F4544BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ressource DROP FOREIGN KEY FK_939F4544BCF5E72D');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE ressource');
    }
}
