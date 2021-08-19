<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210819012718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE auction_image (id INT AUTO_INCREMENT NOT NULL, auction_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, INDEX IDX_8A299D5657B8F0DE (auction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auctions (id INT AUTO_INCREMENT NOT NULL, by_user_id INT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, ends_at DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_72D6E900DC9C2434 (by_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE temp_image (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE auction_image ADD CONSTRAINT FK_8A299D5657B8F0DE FOREIGN KEY (auction_id) REFERENCES auctions (id)');
        $this->addSql('ALTER TABLE auctions ADD CONSTRAINT FK_72D6E900DC9C2434 FOREIGN KEY (by_user_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auction_image DROP FOREIGN KEY FK_8A299D5657B8F0DE');
        $this->addSql('ALTER TABLE auctions DROP FOREIGN KEY FK_72D6E900DC9C2434');
        $this->addSql('DROP TABLE auction_image');
        $this->addSql('DROP TABLE auctions');
        $this->addSql('DROP TABLE temp_image');
        $this->addSql('DROP TABLE users');
    }
}
