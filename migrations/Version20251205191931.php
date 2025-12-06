<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251205191931 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bookmarks (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_78D2C140A76ED395 (user_id), INDEX IDX_78D2C1404B89032C (post_id), UNIQUE INDEX uniq_bookmark_user_post (user_id, post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE follows (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, follower_id INT NOT NULL, followed_id INT NOT NULL, INDEX IDX_4B638A73AC24F853 (follower_id), INDEX IDX_4B638A73D956F010 (followed_id), UNIQUE INDEX uniq_follow_follower_followed (follower_id, followed_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE hashtags (id INT AUTO_INCREMENT NOT NULL, tag VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX uniq_hashtag_tag (tag), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE likes (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_49CA4E7DA76ED395 (user_id), INDEX IDX_49CA4E7D4B89032C (post_id), UNIQUE INDEX uniq_like_user_post (user_id, post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messages (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, read_at DATETIME DEFAULT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, INDEX IDX_DB021E96F624B39D (sender_id), INDEX IDX_DB021E96CD53EDB6 (receiver_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, content VARCHAR(280) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, likes_count INT NOT NULL, retweets_count INT NOT NULL, bookmarks_count INT NOT NULL, replies_count INT NOT NULL, user_id INT NOT NULL, in_reply_to_id INT DEFAULT NULL, repost_of_id INT DEFAULT NULL, INDEX IDX_5A8A6C8DA76ED395 (user_id), INDEX IDX_5A8A6C8DDD92DAB8 (in_reply_to_id), INDEX IDX_5A8A6C8DBCBBA49B (repost_of_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post_hashtag (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, hashtag_id INT NOT NULL, INDEX IDX_675D9D524B89032C (post_id), INDEX IDX_675D9D52FB34EF56 (hashtag_id), UNIQUE INDEX uniq_post_hashtag (post_id, hashtag_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE retweets (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, post_id INT NOT NULL, INDEX IDX_4923CB40A76ED395 (user_id), INDEX IDX_4923CB404B89032C (post_id), UNIQUE INDEX uniq_retweet_user_post (user_id, post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, name VARCHAR(100) DEFAULT NULL, bio VARCHAR(160) DEFAULT NULL, avatar_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE bookmarks ADD CONSTRAINT FK_78D2C140A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE bookmarks ADD CONSTRAINT FK_78D2C1404B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE follows ADD CONSTRAINT FK_4B638A73AC24F853 FOREIGN KEY (follower_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE follows ADD CONSTRAINT FK_4B638A73D956F010 FOREIGN KEY (followed_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7D4B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE messages ADD CONSTRAINT FK_DB021E96CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DDD92DAB8 FOREIGN KEY (in_reply_to_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DBCBBA49B FOREIGN KEY (repost_of_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post_hashtag ADD CONSTRAINT FK_675D9D524B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('ALTER TABLE post_hashtag ADD CONSTRAINT FK_675D9D52FB34EF56 FOREIGN KEY (hashtag_id) REFERENCES hashtags (id)');
        $this->addSql('ALTER TABLE retweets ADD CONSTRAINT FK_4923CB40A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE retweets ADD CONSTRAINT FK_4923CB404B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bookmarks DROP FOREIGN KEY FK_78D2C140A76ED395');
        $this->addSql('ALTER TABLE bookmarks DROP FOREIGN KEY FK_78D2C1404B89032C');
        $this->addSql('ALTER TABLE follows DROP FOREIGN KEY FK_4B638A73AC24F853');
        $this->addSql('ALTER TABLE follows DROP FOREIGN KEY FK_4B638A73D956F010');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7DA76ED395');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7D4B89032C');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96F624B39D');
        $this->addSql('ALTER TABLE messages DROP FOREIGN KEY FK_DB021E96CD53EDB6');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DDD92DAB8');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DBCBBA49B');
        $this->addSql('ALTER TABLE post_hashtag DROP FOREIGN KEY FK_675D9D524B89032C');
        $this->addSql('ALTER TABLE post_hashtag DROP FOREIGN KEY FK_675D9D52FB34EF56');
        $this->addSql('ALTER TABLE retweets DROP FOREIGN KEY FK_4923CB40A76ED395');
        $this->addSql('ALTER TABLE retweets DROP FOREIGN KEY FK_4923CB404B89032C');
        $this->addSql('DROP TABLE bookmarks');
        $this->addSql('DROP TABLE follows');
        $this->addSql('DROP TABLE hashtags');
        $this->addSql('DROP TABLE likes');
        $this->addSql('DROP TABLE messages');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE post_hashtag');
        $this->addSql('DROP TABLE retweets');
        $this->addSql('DROP TABLE user');
    }
}
