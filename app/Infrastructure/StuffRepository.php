<?php

namespace App\Infrastructure;

use DateTime;
use Doctrine\DBAL\Connection;

class StuffRepository extends BaseRepository
{
    const TABLE = 'stuff';
    
    
    /**
     *
     */
    public function createTable()
    {
        $table = $this->schema->createTable(self::TABLE);
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoIncrement' => true]);
        $table->addColumn('status', 'string', ['length' => 10, 'default' => 'pending']);
        $table->addColumn('created', 'datetime', ['default' => (new DateTime())->format('Y-m-d H:i:s')]);
        $table->addColumn('updated', 'datetime', ['notNull' => false]);
        $table->addColumn('processorId', 'integer', ['notNull' => false]);
        $table->setPrimaryKey(['id']);

        $dbPlatform = $this->connection->getSchemaManager()->getDatabasePlatform();
        $this->connection->exec($this->schema->toDropSql($dbPlatform)[0]);
        $this->connection->exec($this->schema->toSql($dbPlatform)[0]);
    }

    /**
     * 
     */
    public function seedDummyData()
    {
        for ($i = 1; $i <= 100; $i++) {
            $this->connection->insert(self::TABLE, []);
        }
    }

    /**
     * @return int
     */
    public function getNumberOfStuff()
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from(self::TABLE);
        return $qb->execute()->rowCount();
    }

    /**
     * @param int $batchSize
     * @return array
     */
    public function getBatch($batchSize)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from(self::TABLE, 't')
            ->where('t.status = :status')
            ->setParameter(':status', 'pending')
            ->setFirstResult(0)
            ->setMaxResults($batchSize);
        return $qb->execute()->fetchAll();
    }

    /**
     * @param int $processorId
     * @param array $ids
     * @return int
     */
    public function updateBatch($processorId, array $ids)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update(self::TABLE, 't')
            ->set('t.status', ':status')
            ->set('t.processorId', ':processorId')
            ->setParameter(':status', 'processed')
            ->setParameter(':processorId', $processorId)
            ->where($qb->expr()->in('id', $ids));
        $qb->execute();
    }
}
