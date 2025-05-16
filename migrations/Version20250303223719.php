<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303223719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prediction (id INT AUTO_INCREMENT NOT NULL, dossier_id INT DEFAULT NULL, hypertension TINYINT(1) NOT NULL, heart_disease TINYINT(1) NOT NULL, smoking_history VARCHAR(255) NOT NULL, bmi DOUBLE PRECISION NOT NULL, hb_a1c_level DOUBLE PRECISION NOT NULL, blood_glucose_level DOUBLE PRECISION NOT NULL, diabete TINYINT(1) NOT NULL, INDEX IDX_36396FC8611C0C56 (dossier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE prediction ADD CONSTRAINT FK_36396FC8611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier_medical (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prediction DROP FOREIGN KEY FK_36396FC8611C0C56');
        $this->addSql('DROP TABLE prediction');
    }
}
