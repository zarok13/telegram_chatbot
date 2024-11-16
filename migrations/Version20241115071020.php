<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241115071020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE chats_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE messages_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE chats (id INT NOT NULL, type VARCHAR(30) NOT NULL, chat_id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2D68180F1A9A7125 ON chats (chat_id)');
        $this->addSql('CREATE TABLE messages (id INT NOT NULL, chat_id INT NOT NULL, message_id INT NOT NULL, state_id SMALLINT NOT NULL, is_bot BOOLEAN NOT NULL, first_name VARCHAR(100) DEFAULT NULL, last_name VARCHAR(100) DEFAULT NULL, text TEXT NOT NULL, language_code VARCHAR(10) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DB021E961A9A7125 ON messages (chat_id)');
        $this->addSql('COMMENT ON COLUMN messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messages.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E961A9A7125 FOREIGN KEY (chat_id) REFERENCES chats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE chats_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE messages_id_seq CASCADE');
        $this->addSql('ALTER TABLE messages DROP CONSTRAINT FK_DB021E961A9A7125');
        $this->addSql('DROP TABLE chats');
        $this->addSql('DROP TABLE messages');
    }
}
