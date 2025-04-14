<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414102027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP INDEX UNIQ_8D93D6493DA5256D, ADD INDEX IDX_8D93D6493DA5256D (image_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD status TINYINT(1) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP INDEX IDX_8D93D6493DA5256D, ADD UNIQUE INDEX UNIQ_8D93D6493DA5256D (image_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP status
        SQL);
    }
}
