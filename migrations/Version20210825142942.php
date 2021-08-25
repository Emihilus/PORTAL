<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210825142942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auction_images (id INT AUTO_INCREMENT NOT NULL, auction_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, order_indicator INT NOT NULL, INDEX IDX_998C125757B8F0DE (auction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auctions (id INT AUTO_INCREMENT NOT NULL, by_user_id INT NOT NULL, title VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, description TEXT DEFAULT NULL, INDEX IDX_72D6E900DC9C2434 (by_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offers (id INT AUTO_INCREMENT NOT NULL, auction_id INT NOT NULL, by_user_id INT NOT NULL, value INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_DA46042757B8F0DE (auction_id), INDEX IDX_DA460427DC9C2434 (by_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE temp_images (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE auction_images ADD CONSTRAINT FK_998C125757B8F0DE FOREIGN KEY (auction_id) REFERENCES auctions (id)');
        $this->addSql('ALTER TABLE auctions ADD CONSTRAINT FK_72D6E900DC9C2434 FOREIGN KEY (by_user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE offers ADD CONSTRAINT FK_DA46042757B8F0DE FOREIGN KEY (auction_id) REFERENCES auctions (id)');
        $this->addSql('ALTER TABLE offers ADD CONSTRAINT FK_DA460427DC9C2434 FOREIGN KEY (by_user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auction_images DROP FOREIGN KEY FK_998C125757B8F0DE');
        $this->addSql('ALTER TABLE offers DROP FOREIGN KEY FK_DA46042757B8F0DE');
        $this->addSql('ALTER TABLE auctions DROP FOREIGN KEY FK_72D6E900DC9C2434');
        $this->addSql('ALTER TABLE offers DROP FOREIGN KEY FK_DA460427DC9C2434');
        $this->addSql('DROP TABLE auction_images');
        $this->addSql('DROP TABLE auctions');
        $this->addSql('DROP TABLE offers');
        $this->addSql('DROP TABLE temp_images');
        $this->addSql('DROP TABLE users');
    }
}
