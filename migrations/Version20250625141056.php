<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625141056 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE invoice_product (invoice_id INT NOT NULL, product_id INT NOT NULL, PRIMARY KEY(invoice_id, product_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2193327E2989F1FD ON invoice_product (invoice_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2193327E4584665A ON invoice_product (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice_product ADD CONSTRAINT FK_2193327E2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice_product ADD CONSTRAINT FK_2193327E4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice_product DROP CONSTRAINT FK_2193327E2989F1FD
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice_product DROP CONSTRAINT FK_2193327E4584665A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE invoice_product
        SQL);
    }
}
