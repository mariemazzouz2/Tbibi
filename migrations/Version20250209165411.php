<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250209165411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, specialite VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, visible TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B6F7494E6B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, medecin_id INT NOT NULL, contenu VARCHAR(255) NOT NULL, date_reponse DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5FB6DEC71E27F6BF (question_id), INDEX IDX_5FB6DEC74F31A84 (medecin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, medecin_id INT NOT NULL, reponse_id INT NOT NULL, valeur VARCHAR(255) NOT NULL, INDEX IDX_5A1085644F31A84 (medecin_id), INDEX IDX_5A108564CF18BB82 (reponse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E6B899279 FOREIGN KEY (patient_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC71E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC74F31A84 FOREIGN KEY (medecin_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A1085644F31A84 FOREIGN KEY (medecin_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564CF18BB82 FOREIGN KEY (reponse_id) REFERENCES reponse (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E6B899279');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC71E27F6BF');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC74F31A84');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A1085644F31A84');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564CF18BB82');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE vote');
    }
}
