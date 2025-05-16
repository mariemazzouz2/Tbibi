<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305001321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE analyse (id INT AUTO_INCREMENT NOT NULL, dossier_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, dateanalyse DATE NOT NULL, donnees_analyse VARCHAR(255) NOT NULL, diagnostic VARCHAR(255) NOT NULL, INDEX IDX_351B0C7E611C0C56 (dossier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie_ev (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, montant_total NUMERIC(10, 2) NOT NULL, date_commande DATE NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_6EEAA67DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, medecin_id INT NOT NULL, patient_id INT NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, commentaire VARCHAR(255) NOT NULL, date_c DATETIME NOT NULL, meet_link LONGTEXT DEFAULT NULL, INDEX IDX_964685A64F31A84 (medecin_id), INDEX IDX_964685A66B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE demande_participation (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, evenement_id INT NOT NULL, statut VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1BD2B3FFFB88E14F (utilisateur_id), INDEX IDX_1BD2B3FFFD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dossier_medical (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, date DATE NOT NULL, fichier VARCHAR(255) NOT NULL, unite VARCHAR(255) NOT NULL, mesure DOUBLE PRECISION NOT NULL, UNIQUE INDEX UNIQ_3581EE62FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, categorie_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, lieu VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_B26681EBCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participe (evenement_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_9FFA8D4FD02F13 (evenement_id), INDEX IDX_9FFA8D4FB88E14F (utilisateur_id), PRIMARY KEY(evenement_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, demande_participation_id INT NOT NULL, message VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_read TINYINT(1) NOT NULL, INDEX IDX_BF5476CA7FECBF45 (demande_participation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notii (id INT AUTO_INCREMENT NOT NULL, touser_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, INDEX IDX_50CC10FA788F388B (touser_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ordonnance (id INT AUTO_INCREMENT NOT NULL, consultation_id INT NOT NULL, description VARCHAR(255) NOT NULL, signature VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_924B326C62FF6CDF (consultation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, commande_id INT DEFAULT NULL, prix DOUBLE PRECISION NOT NULL, type VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_29A5EC2782EA2E54 (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, specialite VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, visible TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B6F7494E6B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, medecin_id INT NOT NULL, contenu VARCHAR(255) NOT NULL, date_reponse DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5FB6DEC71E27F6BF (question_id), INDEX IDX_5FB6DEC74F31A84 (medecin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_token (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, INDEX IDX_452C9EC5FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, telephone INT NOT NULL, adresse VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, taille DOUBLE PRECISION DEFAULT NULL, poids INT DEFAULT NULL, sexe VARCHAR(255) NOT NULL, image VARCHAR(255) DEFAULT NULL, status INT DEFAULT NULL, diplome VARCHAR(255) DEFAULT NULL, specialite VARCHAR(255) DEFAULT NULL, face_encoding LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, medecin_id INT NOT NULL, reponse_id INT NOT NULL, valeur VARCHAR(255) NOT NULL, INDEX IDX_5A1085644F31A84 (medecin_id), INDEX IDX_5A108564CF18BB82 (reponse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A64F31A84 FOREIGN KEY (medecin_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A66B899279 FOREIGN KEY (patient_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE demande_participation ADD CONSTRAINT FK_1BD2B3FFFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE demande_participation ADD CONSTRAINT FK_1BD2B3FFFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE62FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_ev (id)');
        $this->addSql('ALTER TABLE participe ADD CONSTRAINT FK_9FFA8D4FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE participe ADD CONSTRAINT FK_9FFA8D4FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7FECBF45 FOREIGN KEY (demande_participation_id) REFERENCES demande_participation (id)');
        $this->addSql('ALTER TABLE notii ADD CONSTRAINT FK_50CC10FA788F388B FOREIGN KEY (touser_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE ordonnance ADD CONSTRAINT FK_924B326C62FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC2782EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E6B899279 FOREIGN KEY (patient_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC71E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC74F31A84 FOREIGN KEY (medecin_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reset_password_token ADD CONSTRAINT FK_452C9EC5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A1085644F31A84 FOREIGN KEY (medecin_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564CF18BB82 FOREIGN KEY (reponse_id) REFERENCES reponse (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E611C0C56');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A64F31A84');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A66B899279');
        $this->addSql('ALTER TABLE demande_participation DROP FOREIGN KEY FK_1BD2B3FFFB88E14F');
        $this->addSql('ALTER TABLE demande_participation DROP FOREIGN KEY FK_1BD2B3FFFD02F13');
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE62FB88E14F');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EBCF5E72D');
        $this->addSql('ALTER TABLE participe DROP FOREIGN KEY FK_9FFA8D4FD02F13');
        $this->addSql('ALTER TABLE participe DROP FOREIGN KEY FK_9FFA8D4FB88E14F');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7FECBF45');
        $this->addSql('ALTER TABLE notii DROP FOREIGN KEY FK_50CC10FA788F388B');
        $this->addSql('ALTER TABLE ordonnance DROP FOREIGN KEY FK_924B326C62FF6CDF');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC2782EA2E54');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E6B899279');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC71E27F6BF');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC74F31A84');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE reset_password_token DROP FOREIGN KEY FK_452C9EC5FB88E14F');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A1085644F31A84');
        $this->addSql('ALTER TABLE vote DROP FOREIGN KEY FK_5A108564CF18BB82');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('DROP TABLE categorie_ev');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE demande_participation');
        $this->addSql('DROP TABLE dossier_medical');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE participe');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE notii');
        $this->addSql('DROP TABLE ordonnance');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE reset_password_token');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE vote');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
