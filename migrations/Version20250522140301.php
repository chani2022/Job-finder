<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250522140301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE abonnement (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_351268BBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE abonnement_category (abonnement_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_4545A216F1D74413 (abonnement_id), INDEX IDX_4545A21612469DE2 (category_id), PRIMARY KEY(abonnement_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, nom_category VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE experience (id INT AUTO_INCREMENT NOT NULL, nombre_experience VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE media_object (id INT AUTO_INCREMENT NOT NULL, file_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE niveau_etude (id INT AUTO_INCREMENT NOT NULL, niveau_etude VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_NIVEAU_ETUDE (niveau_etude), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, offre_emploi_id INT NOT NULL, is_read TINYINT(1) NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CAB08996ED (offre_emploi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE offre_emploi (id INT AUTO_INCREMENT NOT NULL, type_contrat_id INT DEFAULT NULL, secteur_activite_id INT DEFAULT NULL, niveau_etude_id INT DEFAULT NULL, experience_id INT DEFAULT NULL, user_id INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', date_expired_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_132AD0D1520D03A (type_contrat_id), INDEX IDX_132AD0D15233A7FC (secteur_activite_id), INDEX IDX_132AD0D1FEAD13D1 (niveau_etude_id), INDEX IDX_132AD0D146E90E27 (experience_id), INDEX IDX_132AD0D1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, client_email VARCHAR(255) DEFAULT NULL, client_id VARCHAR(255) DEFAULT NULL, total_amount INT DEFAULT NULL, currency_code VARCHAR(255) DEFAULT NULL, details JSON NOT NULL COMMENT '(DC2Type:json)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE secteur_activite (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, type_secteur VARCHAR(255) NOT NULL, INDEX IDX_5CD9BFE112469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE society (id INT AUTO_INCREMENT NOT NULL, nom_society VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (nom_society), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE token (hash VARCHAR(255) NOT NULL, details LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', after_url LONGTEXT DEFAULT NULL, target_url LONGTEXT NOT NULL, gateway_name VARCHAR(255) NOT NULL, PRIMARY KEY(hash)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE type_contrat (id INT AUTO_INCREMENT NOT NULL, type_contrat VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, society_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, nom VARCHAR(255) DEFAULT NULL, prenom VARCHAR(255) DEFAULT NULL, username VARCHAR(255) NOT NULL, status TINYINT(1) DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_8D93D6493DA5256D (image_id), INDEX IDX_8D93D649E6389D24 (society_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email, username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement ADD CONSTRAINT FK_351268BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement_category ADD CONSTRAINT FK_4545A216F1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnement (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement_category ADD CONSTRAINT FK_4545A21612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAB08996ED FOREIGN KEY (offre_emploi_id) REFERENCES offre_emploi (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D1520D03A FOREIGN KEY (type_contrat_id) REFERENCES type_contrat (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D15233A7FC FOREIGN KEY (secteur_activite_id) REFERENCES secteur_activite (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D1FEAD13D1 FOREIGN KEY (niveau_etude_id) REFERENCES niveau_etude (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D146E90E27 FOREIGN KEY (experience_id) REFERENCES experience (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE secteur_activite ADD CONSTRAINT FK_5CD9BFE112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D6493DA5256D FOREIGN KEY (image_id) REFERENCES media_object (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D649E6389D24 FOREIGN KEY (society_id) REFERENCES society (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BBA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement_category DROP FOREIGN KEY FK_4545A216F1D74413
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement_category DROP FOREIGN KEY FK_4545A21612469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAB08996ED
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D1520D03A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D15233A7FC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D1FEAD13D1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D146E90E27
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE secteur_activite DROP FOREIGN KEY FK_5CD9BFE112469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493DA5256D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E6389D24
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE abonnement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE abonnement_category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE experience
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE media_object
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE niveau_etude
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE offre_emploi
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payment
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE secteur_activite
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE society
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE type_contrat
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
    }
}
