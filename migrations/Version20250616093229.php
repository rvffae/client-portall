<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250616093229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
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
            ALTER TABLE client DROP CONSTRAINT client_company_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_C7440455979B1AD6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP company_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP first_name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP last_name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP email
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP phone
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP city
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP state
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP zip_code
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP country
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP created_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP address
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP city
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP state
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP zip_code
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP country
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP phone
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP email
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP website
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP created_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP CONSTRAINT contact_client_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_4C62E63819EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP client_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP contact_type
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP contact_detail
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP created_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP CONSTRAINT invoice_project_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_90651744166D1F9C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP project_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP invoice_number
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP issue_date
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP due_date
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP amount
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP status
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP created_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice DROP updated_at
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
            ALTER TABLE project DROP name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP description
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP start_date
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP end_date
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP status
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP budget
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP created_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project DROP updated_at
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD company_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD name VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD description TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD start_date DATE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD end_date DATE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD status VARCHAR(50) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD budget NUMERIC(10, 2) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE project ADD CONSTRAINT project_company_id_fkey FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2FB3D0EE979B1AD6 ON project (company_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD company_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD first_name VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD last_name VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD email VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD phone VARCHAR(20) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD address TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD city VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD state VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD zip_code VARCHAR(20) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD country VARCHAR(100) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE client ADD CONSTRAINT client_company_id_fkey FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C7440455979B1AD6 ON client (company_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD project_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD invoice_number VARCHAR(50) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD issue_date DATE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD due_date DATE DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD amount NUMERIC(10, 2) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD status VARCHAR(50) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice ADD CONSTRAINT invoice_project_id_fkey FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_90651744166D1F9C ON invoice (project_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD name VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD address TEXT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD city VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD state VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD zip_code VARCHAR(20) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD country VARCHAR(100) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD phone VARCHAR(20) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD email VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD website VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE company ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD client_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD contact_type VARCHAR(50) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD contact_detail VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE contact ADD CONSTRAINT contact_client_id_fkey FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4C62E63819EB6921 ON contact (client_id)
        SQL);
    }
}
