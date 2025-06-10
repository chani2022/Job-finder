<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250610105305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE candidature (id INT AUTO_INCREMENT NOT NULL, candidat_id INT NOT NULL, offre_emploi_id INT NOT NULL, piece_jointe_id INT NOT NULL, INDEX IDX_E33BD3B88D0EB82 (candidat_id), INDEX IDX_E33BD3B8B08996ED (offre_emploi_id), INDEX IDX_E33BD3B8A3741A05 (piece_jointe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE piece_jointe (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, cv_id INT NOT NULL, lettre_motivation LONGTEXT NOT NULL, INDEX IDX_AB5111D47E3C61F9 (owner_id), INDEX IDX_AB5111D4CFE419E2 (cv_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B88D0EB82 FOREIGN KEY (candidat_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8B08996ED FOREIGN KEY (offre_emploi_id) REFERENCES offre_emploi (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8A3741A05 FOREIGN KEY (piece_jointe_id) REFERENCES piece_jointe (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE piece_jointe ADD CONSTRAINT FK_AB5111D47E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE piece_jointe ADD CONSTRAINT FK_AB5111D4CFE419E2 FOREIGN KEY (cv_id) REFERENCES media_object (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B88D0EB82
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8B08996ED
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8A3741A05
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE piece_jointe DROP FOREIGN KEY FK_AB5111D47E3C61F9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE piece_jointe DROP FOREIGN KEY FK_AB5111D4CFE419E2
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE candidature
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE piece_jointe
        SQL);
    }
}
