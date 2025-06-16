<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616100512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE project_company (project_id INT NOT NULL, company_id INT NOT NULL, PRIMARY KEY(project_id, company_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D9A1052A166D1F9C ON project_company (project_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D9A1052A979B1AD6 ON project_company (company_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_company ADD CONSTRAINT FK_D9A1052A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_company ADD CONSTRAINT FK_D9A1052A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP CONSTRAINT client_company_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C7440455979B1AD6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD adress VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ALTER phone TYPE VARCHAR(10)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ALTER created_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ALTER updated_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client RENAME COLUMN company_id TO company_id_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD CONSTRAINT FK_C744045538B53C32 FOREIGN KEY (company_id_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C744045538B53C32 ON client (company_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD adress VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER zip_code TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER phone TYPE VARCHAR(10)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER created_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER updated_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN company.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN company.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP CONSTRAINT contact_client_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4C62E63819EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ALTER created_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ALTER updated_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact RENAME COLUMN client_id TO client_id_id
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN contact.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN contact.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD CONSTRAINT FK_4C62E638DC2902E0 FOREIGN KEY (client_id_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_4C62E638DC2902E0 ON contact (client_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP CONSTRAINT invoice_project_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_90651744166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER amount TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER created_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER updated_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice RENAME COLUMN project_id TO project_id_id
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD CONSTRAINT FK_906517446C1197C9 FOREIGN KEY (project_id_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_906517446C1197C9 ON invoice (project_id_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP CONSTRAINT project_company_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2FB3D0EE979B1AD6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP company_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER budget TYPE DOUBLE PRECISION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER created_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER updated_at DROP DEFAULT
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN project.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN project.updated_at IS '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_company DROP CONSTRAINT FK_D9A1052A166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project_company DROP CONSTRAINT FK_D9A1052A979B1AD6
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE project_company
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD company_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER budget TYPE NUMERIC(10, 2)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER created_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN project.created_at IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN project.updated_at IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD CONSTRAINT project_company_id_fkey FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2FB3D0EE979B1AD6 ON project (company_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP CONSTRAINT FK_4C62E638DC2902E0
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_4C62E638DC2902E0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ALTER created_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact RENAME COLUMN client_id_id TO client_id
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN contact.created_at IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN contact.updated_at IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD CONSTRAINT contact_client_id_fkey FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4C62E63819EB6921 ON contact (client_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD address TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP adress
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER zip_code TYPE VARCHAR(20)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER phone TYPE VARCHAR(20)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER created_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN company.created_at IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN company.updated_at IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP CONSTRAINT FK_906517446C1197C9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_906517446C1197C9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER amount TYPE NUMERIC(10, 2)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER created_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice RENAME COLUMN project_id_id TO project_id
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice.created_at IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN invoice.updated_at IS NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD CONSTRAINT invoice_project_id_fkey FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_90651744166D1F9C ON invoice (project_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP CONSTRAINT FK_C744045538B53C32
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C744045538B53C32
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD address TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP adress
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ALTER phone TYPE VARCHAR(20)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ALTER created_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ALTER updated_at SET DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client RENAME COLUMN company_id_id TO company_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD CONSTRAINT client_company_id_fkey FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C7440455979B1AD6 ON client (company_id)
        SQL);
    }
}
