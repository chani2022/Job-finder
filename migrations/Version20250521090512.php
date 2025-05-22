<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521090512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql(<<<'SQL'
        //     CREATE TABLE offre_emploi (id INT AUTO_INCREMENT NOT NULL, type_contrat_id INT DEFAULT NULL, secteur_activite_id INT DEFAULT NULL, niveau_etude_id INT DEFAULT NULL, experience_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date_created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', date_expired_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_132AD0D1520D03A (type_contrat_id), INDEX IDX_132AD0D15233A7FC (secteur_activite_id), INDEX IDX_132AD0D1FEAD13D1 (niveau_etude_id), INDEX IDX_132AD0D146E90E27 (experience_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D1520D03A FOREIGN KEY (type_contrat_id) REFERENCES type_contrat (id)
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D15233A7FC FOREIGN KEY (secteur_activite_id) REFERENCES secteur_activite (id)
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D1FEAD13D1 FOREIGN KEY (niveau_etude_id) REFERENCES niveau_etude (id)
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE offre_emploi ADD CONSTRAINT FK_132AD0D146E90E27 FOREIGN KEY (experience_id) REFERENCES experience (id)
        // SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D1520D03A
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D15233A7FC
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D1FEAD13D1
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE offre_emploi DROP FOREIGN KEY FK_132AD0D146E90E27
        // SQL);
        // $this->addSql(<<<'SQL'
        //     DROP TABLE offre_emploi
        // SQL);
    }
}
