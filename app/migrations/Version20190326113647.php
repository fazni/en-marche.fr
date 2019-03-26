<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190326113647 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE assessor_managed_areas (
          id INT AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          codes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          UNIQUE INDEX UNIQ_9D55225025F06C53 (adherent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          assessor_managed_areas 
        ADD 
          CONSTRAINT FK_9D55225025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE assessor_managed_areas');
    }
}
