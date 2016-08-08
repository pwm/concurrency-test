<?php

namespace App\Infrastructure;

use Closure;
use \Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Exception;

class BaseRepository
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Schema
     */
    protected $schema;


    /**
     * @param Connection $connection
     * @param Schema $schema
     */
    public function __construct(Connection $connection, Schema $schema)
    {
        $this->connection = $connection;
        $this->schema = $schema;
        /**
         * Connection::TRANSACTION_READ_UNCOMMITTED
         * Connection::TRANSACTION_READ_COMMITTED
         * Connection::TRANSACTION_REPEATABLE_READ
         * Connection::TRANSACTION_SERIALIZABLE
         */
        $this->connection->setTransactionIsolation(Connection::TRANSACTION_REPEATABLE_READ);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param Closure $fn
     * @throws Exception
     */
    public function tr(Closure $fn)
    {
        $this->connection->transactional($fn);
    }
}
