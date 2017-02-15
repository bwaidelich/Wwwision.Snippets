<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Initial migration, adding a table for the SnippetSource model
 */
class Version20160212112525 extends AbstractMigration
{

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("CREATE TABLE wwwision_snippets_domain_model_snippetsource (tenantid VARCHAR(255) NOT NULL, snippetid VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, PRIMARY KEY(tenantid, snippetid)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("DROP TABLE wwwision_snippets_domain_model_snippetsource");
    }
}