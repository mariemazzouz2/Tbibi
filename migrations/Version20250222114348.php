<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250222114348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE analyse (id INT AUTO_INCREMENT NOT NULL, dossier_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, dateanalyse DATE NOT NULL, donnees_analyse VARCHAR(255) NOT NULL, diagnostic VARCHAR(255) NOT NULL, INDEX IDX_351B0C7E611C0C56 (dossier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE analyse ADD CONSTRAINT FK_351B0C7E611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE evenement_utilisateur DROP FOREIGN KEY FK_8C897598FD02F13');
        $this->addSql('ALTER TABLE evenement_utilisateur DROP FOREIGN KEY FK_8C897598FB88E14F');
        $this->addSql('ALTER TABLE suivie DROP FOREIGN KEY FK_92F358217750B79F');
        $this->addSql('DROP TABLE evenement_utilisateur');
        $this->addSql('DROP TABLE suivie');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DFB88E14F');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DF347EFB');
        $this->addSql('DROP INDEX IDX_6EEAA67DFB88E14F ON commande');
        $this->addSql('DROP INDEX IDX_6EEAA67DF347EFB ON commande');
        $this->addSql('ALTER TABLE commande ADD user_id INT DEFAULT NULL, DROP utilisateur_id, DROP produit_id');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DA76ED395 ON commande (user_id)');
        $this->addSql('ALTER TABLE consultation ADD meet_link LONGTEXT DEFAULT NULL, DROP lien, CHANGE date_c date_c DATETIME NOT NULL');
        $this->addSql('ALTER TABLE dossier_medical ADD fichier VARCHAR(255) NOT NULL, ADD unite VARCHAR(255) NOT NULL, ADD mesure DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE evenement ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE produit ADD commande_id INT DEFAULT NULL, ADD prix DOUBLE PRECISION NOT NULL, ADD description VARCHAR(255) NOT NULL, DROP prix_p, DROP stock, CHANGE description_p image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC2782EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('CREATE INDEX IDX_29A5EC2782EA2E54 ON produit (commande_id)');
        $this->addSql('ALTER TABLE utilisateur ADD image VARCHAR(255) DEFAULT NULL, ADD status INT DEFAULT NULL, ADD diplome VARCHAR(255) DEFAULT NULL, CHANGE taille taille DOUBLE PRECISION DEFAULT NULL, CHANGE poids poids INT DEFAULT NULL, CHANGE specialite specialite VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evenement_utilisateur (evenement_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_8C897598FB88E14F (utilisateur_id), INDEX IDX_8C897598FD02F13 (evenement_id), PRIMARY KEY(evenement_id, utilisateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE suivie (id INT AUTO_INCREMENT NOT NULL, dossier_medical_id INT NOT NULL, type_s VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_suivie DATE NOT NULL, donnees VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_92F358217750B79F (dossier_medical_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE evenement_utilisateur ADD CONSTRAINT FK_8C897598FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_utilisateur ADD CONSTRAINT FK_8C897598FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE suivie ADD CONSTRAINT FK_92F358217750B79F FOREIGN KEY (dossier_medical_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E611C0C56');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE analyse');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('DROP INDEX IDX_6EEAA67DA76ED395 ON commande');
        $this->addSql('ALTER TABLE commande ADD utilisateur_id INT NOT NULL, ADD produit_id INT NOT NULL, DROP user_id');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DFB88E14F ON commande (utilisateur_id)');
        $this->addSql('CREATE INDEX IDX_6EEAA67DF347EFB ON commande (produit_id)');
        $this->addSql('ALTER TABLE consultation ADD lien VARCHAR(255) NOT NULL, DROP meet_link, CHANGE date_c date_c DATE NOT NULL');
        $this->addSql('ALTER TABLE dossier_medical DROP fichier, DROP unite, DROP mesure');
        $this->addSql('ALTER TABLE evenement DROP image');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC2782EA2E54');
        $this->addSql('DROP INDEX IDX_29A5EC2782EA2E54 ON produit');
        $this->addSql('ALTER TABLE produit ADD description_p VARCHAR(255) NOT NULL, ADD prix_p INT NOT NULL, ADD stock INT NOT NULL, DROP commande_id, DROP prix, DROP image, DROP description');
        $this->addSql('ALTER TABLE utilisateur DROP image, DROP status, DROP diplome, CHANGE taille taille DOUBLE PRECISION NOT NULL, CHANGE poids poids INT NOT NULL, CHANGE specialite specialite VARCHAR(255) NOT NULL');
    }
}
