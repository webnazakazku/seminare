<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200221125552 extends AbstractMigration
{

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE subevent_skaut_is_course DROP FOREIGN KEY FK_EF3BE1D3D86001CA');
        $this->addSql('DROP TABLE skaut_is_course');
        $this->addSql('DROP TABLE subevent_skaut_is_course');
        $this->addSql('ALTER TABLE role DROP synced_with_skaut_is');
        $this->addSql('DROP INDEX UNIQ_8D93D64970B8EE93 ON user');
        $this->addSql('DROP INDEX UNIQ_8D93D6492F498706 ON user');
        $this->addSql('ALTER TABLE user DROP member, DROP skautis_user_id, DROP skautis_person_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE skaut_is_course (id INT AUTO_INCREMENT NOT NULL, skaut_is_course_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE subevent_skaut_is_course (subevent_id INT NOT NULL, skaut_is_course_id INT NOT NULL, INDEX IDX_EF3BE1D37A675502 (subevent_id), INDEX IDX_EF3BE1D3D86001CA (skaut_is_course_id), PRIMARY KEY(subevent_id, skaut_is_course_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE subevent_skaut_is_course ADD CONSTRAINT FK_EF3BE1D37A675502 FOREIGN KEY (subevent_id) REFERENCES subevent (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE subevent_skaut_is_course ADD CONSTRAINT FK_EF3BE1D3D86001CA FOREIGN KEY (skaut_is_course_id) REFERENCES skaut_is_course (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role ADD synced_with_skaut_is TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user ADD member TINYINT(1) NOT NULL, ADD skautis_user_id INT DEFAULT NULL, ADD skautis_person_id INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64970B8EE93 ON user (skautis_user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6492F498706 ON user (skautis_person_id)');
    }

}
