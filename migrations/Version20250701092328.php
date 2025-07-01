<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701092328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE client_user (client_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(client_id, user_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5C0F152B19EB6921 ON client_user (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5C0F152BA76ED395 ON client_user (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_client (user_id INT NOT NULL, client_id INT NOT NULL, PRIMARY KEY(user_id, client_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A2161F68A76ED395 ON user_client (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_A2161F6819EB6921 ON user_client (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client_user ADD CONSTRAINT FK_5C0F152B19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client_user ADD CONSTRAINT FK_5C0F152BA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_client ADD CONSTRAINT FK_A2161F68A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_client ADD CONSTRAINT FK_A2161F6819EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD userr_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD useros_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD CONSTRAINT FK_4C62E638DF0FD358 FOREIGN KEY (userr_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD CONSTRAINT FK_4C62E6381A6E3803 FOREIGN KEY (useros_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4C62E638DF0FD358 ON contact (userr_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4C62E6381A6E3803 ON contact (useros_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client_user DROP CONSTRAINT FK_5C0F152B19EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client_user DROP CONSTRAINT FK_5C0F152BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_client DROP CONSTRAINT FK_A2161F68A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_client DROP CONSTRAINT FK_A2161F6819EB6921
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE client_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_client
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP CONSTRAINT FK_4C62E638DF0FD358
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP CONSTRAINT FK_4C62E6381A6E3803
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4C62E638DF0FD358
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4C62E6381A6E3803
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP userr_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP useros_id
        SQL);
    }
}
