<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216150329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE suivie DROP FOREIGN KEY FK_92F358217750B79F');
        $this->addSql('DROP TABLE fichier');
        $this->addSql('DROP TABLE suivie');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE62FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3581EE62FB88E14F ON dossier_medical (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fichier (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE suivie (id INT AUTO_INCREMENT NOT NULL, dossier_medical_id INT NOT NULL, type_s VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_suivie DATE NOT NULL, donnees VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_92F358217750B79F (dossier_medical_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE suivie ADD CONSTRAINT FK_92F358217750B79F FOREIGN KEY (dossier_medical_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE62FB88E14F');
        $this->addSql('DROP INDEX UNIQ_3581EE62FB88E14F ON dossier_medical');
    }
}
