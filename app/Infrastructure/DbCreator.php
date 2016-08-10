<?php

namespace App\Infrastructure;

use DateTimeImmutable;
use Doctrine\DBAL\DBALException;
use \Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class DbCreator
{
    /**
     * @var int
     */
    private $numberOfRows;


    /**
     * @param Connection $connection
     * @param Schema $schema
     */
    public function __construct(Connection $connection, Schema $schema)
    {
        $this->connection = $connection;
        $this->schema = $schema;
        $this->platform = $this->connection->getSchemaManager()->getDatabasePlatform();
    }
    
    /**
     * @param int $numberOfRows
     */
    public function setNumberOfRows($numberOfRows)
    {
        $this->numberOfRows = $numberOfRows;
    }

    /**
     * @throws DBALException
     */
    public function setUp()
    {
        $this->dropTables();
        $this->createBatchLockTableSchema();
        $this->createStuffTableSchema();
        $this->executeSchemaSql();
        $this->seedDummyData();
    }

    /**
     * @throws DBALException
     */
    private function dropTables()
    {
        $this->connection->exec('drop table if exists batchLock');
        $this->connection->exec('drop table if exists stuff');
    }
    
    /**
     *
     */
    private function createBatchLockTableSchema()
    {
        $table = $this->schema->createTable('batchLock');
        $table->addColumn('batchId', 'integer', ['unsigned' => true, 'unique' => true]);
        $table->addUniqueIndex(['batchId']);
    }

    /**
     *
     */
    private function createStuffTableSchema()
    {
        $table = $this->schema->createTable('stuff');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoIncrement' => true]);
        $table->addColumn('status', 'string', ['length' => 10, 'default' => 'pending']);
        $table->addColumn('created', 'datetime', ['default' => (new DateTimeImmutable())->format('Y-m-d H:i:s')]);
        $table->addColumn('updated', 'datetime', ['notNull' => false]);
        $table->addColumn('pid', 'integer', ['notNull' => false]);
        $table->addColumn('timesProcessed', 'integer', ['default' => 0]);
        $table->setPrimaryKey(['id']);
    }

    /**
     * @throws DBALException
     */
    private function executeSchemaSql()
    {
        $schemaSql = $this->schema->toSql($this->platform);
        foreach ($schemaSql as $statement) {
            $this->connection->exec($statement);
        }
    }

    /**
     *
     */
    private function seedDummyData()
    {
        for ($i = 1; $i <= $this->numberOfRows; $i++) {
            $this->connection->insert('stuff', []);
        }
    }
}
