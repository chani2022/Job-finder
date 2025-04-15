<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415085536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE society (id INT AUTO_INCREMENT NOT NULL, nom_society VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP INDEX IDX_8D93D6493DA5256D, ADD UNIQUE INDEX UNIQ_8D93D6493DA5256D (image_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD society_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D649E6389D24 FOREIGN KEY (society_id) REFERENCES society (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8D93D649E6389D24 ON user (society_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E6389D24
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE society
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP INDEX UNIQ_8D93D6493DA5256D, ADD INDEX IDX_8D93D6493DA5256D (image_id)
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8D93D649E6389D24 ON user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP society_id
        SQL);
    }
}
