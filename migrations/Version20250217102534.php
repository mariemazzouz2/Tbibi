<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250217102534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse CHANGE données_analyse donnees_analyse LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE utilisateur ADD image VARCHAR(255) DEFAULT NULL, ADD status INT DEFAULT NULL, ADD diplome VARCHAR(255) DEFAULT NULL, CHANGE taille taille DOUBLE PRECISION DEFAULT NULL, CHANGE poids poids INT DEFAULT NULL, CHANGE specialite specialite VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse CHANGE donnees_analyse données_analyse LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('ALTER TABLE utilisateur DROP image, DROP status, DROP diplome, CHANGE taille taille DOUBLE PRECISION NOT NULL, CHANGE poids poids INT NOT NULL, CHANGE specialite specialite VARCHAR(255) NOT NULL');
    }
}
