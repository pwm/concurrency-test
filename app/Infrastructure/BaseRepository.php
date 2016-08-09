<?php

namespace App\Infrastructure;

use \Doctrine\DBAL\Connection;
use Closure;
use Exception;

abstract class BaseRepository
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    private static $isolationLevels = [
        1 => Connection::TRANSACTION_READ_UNCOMMITTED,
        2 => Connection::TRANSACTION_READ_COMMITTED,
        3 => Connection::TRANSACTION_REPEATABLE_READ, // Default
        4 => Connection::TRANSACTION_SERIALIZABLE,
    ];
    

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        //$this->connection->setTransactionIsolation(self::$isolationLevels[3]);
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
