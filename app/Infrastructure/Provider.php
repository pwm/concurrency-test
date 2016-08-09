<?php

namespace App\Infrastructure;

use Doctrine\Common\ClassLoader;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Exception\LogicException;

class Provider implements ServiceProviderInterface
{
    /**
     * @var array
     */
    private $dbConfig;


    /**
     * @param array $dbConfig
     */
    public function __construct(array $dbConfig)
    {
        $this->dbConfig = $dbConfig;
    }

    /**
     * @param Container $container
     * @throws LogicException
     * @throws DBALException
     */
    public function register(Container $container)
    {
        $container[DbCreator::class] = new DbCreator($this->getDoctrineConnection(), new Schema());
        $container[StuffRepository::class] = new StuffRepository($this->getDoctrineConnection());
    }

    /**
     * @return Connection
     * @throws DBALException
     */
    private function getDoctrineConnection()
    {
        static $connection;
        if ($connection instanceof Connection) {
            return $connection;
        }

        $classLoader = new ClassLoader('Doctrine', '../../vendor/doctrine/');
        $classLoader->register();

        $config = new Configuration();
        $connectionParams = [
            'driver'   => $this->dbConfig['type'],
            'host'     => $this->dbConfig['host'],
            'user'     => $this->dbConfig['user'],
            'password' => $this->dbConfig['pass'],
            'dbname'   => $this->dbConfig['name'],
        ];

        $connection = DriverManager::getConnection($connectionParams, $config);
        $connection->connect();
        return $connection;
    }
}
