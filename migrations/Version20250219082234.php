<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219082234 extends AbstractMigration
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
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE suivie (id INT AUTO_INCREMENT NOT NULL, dossier_medical_id INT NOT NULL, type_s VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_suivie DATE NOT NULL, donnees VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_92F358217750B79F (dossier_medical_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE suivie ADD CONSTRAINT FK_92F358217750B79F FOREIGN KEY (dossier_medical_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE analyse DROP FOREIGN KEY FK_351B0C7E611C0C56');
        $this->addSql('DROP TABLE analyse');
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
    }
}
