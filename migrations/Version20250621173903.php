<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250621173903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_status ON tasks (status)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_priority ON tasks (priority)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_owner_status ON tasks (owner_id, status)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_owner_priority ON tasks (owner_id, priority)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE FULLTEXT INDEX idx_fulltext_title_description ON tasks (title, description)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX idx_status ON tasks
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_priority ON tasks
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_owner_status ON tasks
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_owner_priority ON tasks
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_fulltext_title_description ON tasks
        SQL);
    }
}
