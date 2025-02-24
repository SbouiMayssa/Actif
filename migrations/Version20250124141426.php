<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250124141426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actif (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, etat VARCHAR(255) NOT NULL, date_acquisation DATE NOT NULL, INDEX IDX_8F5250264D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE actif_employer (actif_id INT NOT NULL, employer_id INT NOT NULL, INDEX IDX_B5F966A140E1C722 (actif_id), INDEX IDX_B5F966A141CD9E7A (employer_id), PRIMARY KEY(actif_id, employer_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE emplacement (id INT AUTO_INCREMENT NOT NULL, adresse VARCHAR(70) NOT NULL, batiment VARCHAR(25) NOT NULL, num_etage INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE employer (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(30) NOT NULL, prenom VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE actif ADD CONSTRAINT FK_8F5250264D218E FOREIGN KEY (location_id) REFERENCES emplacement (id)');
        $this->addSql('ALTER TABLE actif_employer ADD CONSTRAINT FK_B5F966A140E1C722 FOREIGN KEY (actif_id) REFERENCES actif (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE actif_employer ADD CONSTRAINT FK_B5F966A141CD9E7A FOREIGN KEY (employer_id) REFERENCES employer (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actif DROP FOREIGN KEY FK_8F5250264D218E');
        $this->addSql('ALTER TABLE actif_employer DROP FOREIGN KEY FK_B5F966A140E1C722');
        $this->addSql('ALTER TABLE actif_employer DROP FOREIGN KEY FK_B5F966A141CD9E7A');
        $this->addSql('DROP TABLE actif');
        $this->addSql('DROP TABLE actif_employer');
        $this->addSql('DROP TABLE emplacement');
        $this->addSql('DROP TABLE employer');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
