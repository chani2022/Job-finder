<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520081048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE secteur_activite ADD category_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE secteur_activite ADD CONSTRAINT FK_5CD9BFE112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5CD9BFE112469DE2 ON secteur_activite (category_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE secteur_activite DROP FOREIGN KEY FK_5CD9BFE112469DE2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5CD9BFE112469DE2 ON secteur_activite
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE secteur_activite DROP category_id
        SQL);
    }
}
