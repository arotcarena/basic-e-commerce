<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230606083236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase CHANGE ref ref VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE purchase_line DROP FOREIGN KEY FK_A1A77C954584665A');
        $this->addSql('DROP INDEX IDX_A1A77C954584665A ON purchase_line');
        $this->addSql('ALTER TABLE purchase_line ADD product JSON NOT NULL, DROP product_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE purchase CHANGE ref ref VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE purchase_line DROP FOREIGN KEY FK_A1A77C95558FBEB9');
        $this->addSql('ALTER TABLE purchase_line ADD product_id INT NOT NULL, DROP product');
        $this->addSql('ALTER TABLE purchase_line ADD CONSTRAINT FK_A1A77C954584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_A1A77C954584665A ON purchase_line (product_id)');
    }
}