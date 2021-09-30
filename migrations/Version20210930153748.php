<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210930153748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auction_images (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, auction_id INTEGER DEFAULT NULL, filename VARCHAR(255) NOT NULL, order_indicator INTEGER NOT NULL)');
        $this->addSql('CREATE INDEX IDX_998C125757B8F0DE ON auction_images (auction_id)');
        $this->addSql('CREATE TABLE auctions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, by_user_id INTEGER NOT NULL, title VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, description CLOB DEFAULT NULL, is_deleted BOOLEAN NOT NULL, notification_handled BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_72D6E900DC9C2434 ON auctions (by_user_id)');
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, auction_id INTEGER DEFAULT NULL, by_user_id INTEGER NOT NULL, reply_to_id INTEGER DEFAULT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, value INTEGER DEFAULT NULL, notification_handled BOOLEAN NOT NULL, is_deleted BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_9474526C57B8F0DE ON comment (auction_id)');
        $this->addSql('CREATE INDEX IDX_9474526CDC9C2434 ON comment (by_user_id)');
        $this->addSql('CREATE INDEX IDX_9474526CFFDF7169 ON comment (reply_to_id)');
        $this->addSql('CREATE TABLE likes (comment_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(comment_id, user_id))');
        $this->addSql('CREATE INDEX IDX_49CA4E7DF8697D13 ON likes (comment_id)');
        $this->addSql('CREATE INDEX IDX_49CA4E7DA76ED395 ON likes (user_id)');
        $this->addSql('CREATE TABLE dislikes (comment_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(comment_id, user_id))');
        $this->addSql('CREATE INDEX IDX_2DF3BE11F8697D13 ON dislikes (comment_id)');
        $this->addSql('CREATE INDEX IDX_2DF3BE11A76ED395 ON dislikes (user_id)');
        $this->addSql('CREATE TABLE notification (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, recipient_user_id INTEGER NOT NULL, message CLOB NOT NULL, seen_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, related_entity CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('CREATE INDEX IDX_BF5476CAB15EFB97 ON notification (recipient_user_id)');
        $this->addSql('CREATE TABLE offers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, auction_id INTEGER NOT NULL, by_user_id INTEGER DEFAULT NULL, value INTEGER NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_DA46042757B8F0DE ON offers (auction_id)');
        $this->addSql('CREATE INDEX IDX_DA460427DC9C2434 ON offers (by_user_id)');
        $this->addSql('CREATE TABLE reset_password_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , expires_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id)');
        $this->addSql('CREATE TABLE temp_images (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, token VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_verified BOOLEAN NOT NULL, is_banned BOOLEAN NOT NULL, phone VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE user_auction (user_id INTEGER NOT NULL, auction_id INTEGER NOT NULL, PRIMARY KEY(user_id, auction_id))');
        $this->addSql('CREATE INDEX IDX_86E9EB99A76ED395 ON user_auction (user_id)');
        $this->addSql('CREATE INDEX IDX_86E9EB9957B8F0DE ON user_auction (auction_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE auction_images');
        $this->addSql('DROP TABLE auctions');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE likes');
        $this->addSql('DROP TABLE dislikes');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE offers');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE temp_images');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE user_auction');
    }
}
