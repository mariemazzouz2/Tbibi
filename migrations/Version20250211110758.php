<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250211110758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE exam DROP FOREIGN KEY FK_38BBA6C67750B79F');
        $this->addSql('ALTER TABLE mesure DROP FOREIGN KEY FK_5F1B6E707750B79F');
        $this->addSql('DROP TABLE exam');
        $this->addSql('DROP TABLE mesure');
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE62A76ED395');
        $this->addSql('DROP INDEX UNIQ_3581EE62A76ED395 ON dossier_medical');
        $this->addSql('ALTER TABLE dossier_medical ADD utilisateur_id INT NOT NULL, DROP user_id');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE62FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3581EE62FB88E14F ON dossier_medical (utilisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE exam (id INT AUTO_INCREMENT NOT NULL, dossier_medical_id INT DEFAULT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, diagnostic VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, données LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:array)\', date DATE NOT NULL, INDEX IDX_38BBA6C67750B79F (dossier_medical_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE mesure (id INT AUTO_INCREMENT NOT NULL, dossier_medical_id INT DEFAULT NULL, type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, valeur DOUBLE PRECISION NOT NULL, unité VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date DATE NOT NULL, INDEX IDX_5F1B6E707750B79F (dossier_medical_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE exam ADD CONSTRAINT FK_38BBA6C67750B79F FOREIGN KEY (dossier_medical_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE mesure ADD CONSTRAINT FK_5F1B6E707750B79F FOREIGN KEY (dossier_medical_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE62FB88E14F');
        $this->addSql('DROP INDEX UNIQ_3581EE62FB88E14F ON dossier_medical');
        $this->addSql('ALTER TABLE dossier_medical ADD user_id INT DEFAULT NULL, DROP utilisateur_id');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE62A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3581EE62A76ED395 ON dossier_medical (user_id)');
    }
}
