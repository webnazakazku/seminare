<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200223174211 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE faq');
        $this->addSql('DROP TABLE faq_content');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE faq (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, question LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, answer LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, public TINYINT(1) NOT NULL, position INT NOT NULL, INDEX IDX_E8FF75CCF675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE faq_content (id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE faq ADD CONSTRAINT FK_E8FF75CCF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE faq_content ADD CONSTRAINT FK_E5B45BF2BF396750 FOREIGN KEY (id) REFERENCES content (id) ON DELETE CASCADE');
    }
}
