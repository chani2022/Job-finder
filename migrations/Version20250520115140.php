<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520115140 extends AbstractMigration
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
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE abonnement ADD CONSTRAINT FK_351268BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE abonnement_category ADD CONSTRAINT FK_4545A216F1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnement (id) ON DELETE CASCADE
        // SQL);
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE abonnement_category ADD CONSTRAINT FK_4545A21612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE
        // SQL);
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
        // $this->addSql(<<<'SQL'
        //     ALTER TABLE abonnement_category DROP FOREIGN KEY FK_4545A21612469DE2
        // SQL);
        // $this->addSql(<<<'SQL'
        //     DROP TABLE abonnement
        // SQL);
        // $this->addSql(<<<'SQL'
        //     DROP TABLE abonnement_category
        // SQL);
    }
}
