<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219062741 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE analyse (id INT AUTO_INCREMENT NOT NULL, dossier_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, dateanalyse DATE NOT NULL, donnees_analyse VARCHAR(255) NOT NULL, diagnostic VARCHAR(255) NOT NULL, INDEX IDX_351B0C7E611C0C56 (dossier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE suivie DROP FOREIGN KEY FK_92F358217750B79F');
        $this->addSql('DROP TABLE suivie');
        $this->addSql('ALTER TABLE dossier_medical ADD fichier VARCHAR(255) NOT NULL, ADD unite VARCHAR(255) NOT NULL, ADD mesure DOUBLE PRECISION NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE suivie (id INT AUTO_INCREMENT NOT NULL, dossier_medical_id INT NOT NULL, type_s VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_suivie DATE NOT NULL, donnees VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_92F358217750B79F (dossier_medical_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE suivie ADD CONSTRAINT FK_92F358217750B79F FOREIGN KEY (dossier_medical_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E611C0C56');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('ALTER TABLE dossier_medical DROP fichier, DROP unite, DROP mesure');
    }
}
