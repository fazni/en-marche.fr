<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190327113701 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE assessor_requests (
          id INT AUTO_INCREMENT NOT NULL, 
          vote_place_id INT DEFAULT NULL, 
          gender VARCHAR(6) NOT NULL, 
          last_name VARCHAR(50) NOT NULL, 
          first_name VARCHAR(100) NOT NULL, 
          birth_name VARCHAR(50) DEFAULT NULL, 
          birthdate DATE NOT NULL, 
          birth_city VARCHAR(15) NOT NULL, 
          address VARCHAR(150) NOT NULL, 
          postal_code VARCHAR(15) NOT NULL, 
          city VARCHAR(15) NOT NULL, 
          vote_city VARCHAR(15) NOT NULL, 
          office_number VARCHAR(10) NOT NULL, 
          email_address VARCHAR(255) NOT NULL, 
          phone VARCHAR(35) NOT NULL COMMENT \'(DC2Type:phone_number)\', 
          assessor_city VARCHAR(15) NOT NULL, 
          office VARCHAR(15) NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_26BC800F3F90B30 (vote_place_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE assessor_request_vote_place_wishes (
          assessor_request_id INT NOT NULL, 
          vote_place_id INT NOT NULL, 
          INDEX IDX_2EFFDE111BD1903D (assessor_request_id), 
          INDEX IDX_2EFFDE11F3F90B30 (vote_place_id), 
          PRIMARY KEY(
            assessor_request_id, vote_place_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          assessor_requests 
        ADD 
          CONSTRAINT FK_26BC800F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id)');
        $this->addSql('ALTER TABLE 
          assessor_request_vote_place_wishes 
        ADD 
          CONSTRAINT FK_2EFFDE111BD1903D FOREIGN KEY (assessor_request_id) REFERENCES assessor_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          assessor_request_vote_place_wishes 
        ADD 
          CONSTRAINT FK_2EFFDE11F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE assessor_request_vote_place_wishes DROP FOREIGN KEY FK_2EFFDE111BD1903D');
        $this->addSql('DROP TABLE assessor_requests');
        $this->addSql('DROP TABLE assessor_request_vote_place_wishes');
    }
}
