<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180619095438 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE advert (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, title VARCHAR(150) NOT NULL, content VARCHAR(30000) NOT NULL, post_date DATETIME DEFAULT NULL, INDEX IDX_54F1F40BF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, targeting_user_id INT NOT NULL, comment INT NOT NULL, mark DOUBLE PRECISION DEFAULT NULL, INDEX IDX_9474526CF675F31B (author_id), INDEX IDX_9474526C50910497 (targeting_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE job_request (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, advert_id INT NOT NULL, status TINYINT(1) NOT NULL, INDEX IDX_A1783804F675F31B (author_id), INDEX IDX_A1783804D07ECCB6 (advert_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(320) NOT NULL, name VARCHAR(60) NOT NULL, firstname VARCHAR(60) NOT NULL, password VARCHAR(60) NOT NULL, active TINYINT(1) NOT NULL, admin_level INT NOT NULL, last_connection DATETIME DEFAULT NULL, register_ip VARCHAR(255) NOT NULL, activation_token VARCHAR(32) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE advert ADD CONSTRAINT FK_54F1F40BF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C50910497 FOREIGN KEY (targeting_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_request ADD CONSTRAINT FK_A1783804F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_request ADD CONSTRAINT FK_A1783804D07ECCB6 FOREIGN KEY (advert_id) REFERENCES advert (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE job_request DROP FOREIGN KEY FK_A1783804D07ECCB6');
        $this->addSql('ALTER TABLE advert DROP FOREIGN KEY FK_54F1F40BF675F31B');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C50910497');
        $this->addSql('ALTER TABLE job_request DROP FOREIGN KEY FK_A1783804F675F31B');
        $this->addSql('DROP TABLE advert');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE job_request');
        $this->addSql('DROP TABLE user');
    }
}
