<?php

namespace App\Infrastructure;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use PDO;

class StuffRepository extends BaseRepository
{
    const TABLE = 'stuff';
    const NUMBER_OF_ENTRIES = 100;
    
    
    /**
     *
     */
    public function createTable()
    {
        $table = $this->schema->createTable(self::TABLE);
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoIncrement' => true]);
        $table->addColumn('status', 'string', ['length' => 10, 'default' => 'pending']);
        $table->addColumn('created', 'datetime', ['default' => (new DateTimeImmutable())->format('Y-m-d H:i:s')]);
        $table->addColumn('updated', 'datetime', ['notNull' => false]);
        $table->addColumn('processorId', 'integer', ['notNull' => false]);
        $table->setPrimaryKey(['id']);

        $dbPlatform = $this->connection->getSchemaManager()->getDatabasePlatform();
        //@todo if exists
        $this->connection->exec($this->schema->toDropSql($dbPlatform)[0]);
        //@todo if not exists
        $this->connection->exec($this->schema->toSql($dbPlatform)[0]);
    }

    /**
     * 
     */
    public function seedDummyData()
    {
        for ($i = 1; $i <= self::NUMBER_OF_ENTRIES; $i++) {
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
     * @throws DBALException
     */
    public function getBatch($batchSize)
    {
        $sql = 'select * from stuff where status = :status limit :limit LOCK IN SHARE MODE';
        // here it will simply wait till another process does thr work and then
        // implicitly reads the next batch
        //$sql = 'select * from stuff where status = :status limit :limit FOR UPDATE';
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('status', 'pending');
        $stmt->bindValue('limit', $batchSize, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();

        //$qb = $this->connection->createQueryBuilder();
        //$qb
        //    ->select('*')
        //    ->from(self::TABLE, 't')
        //    ->where('t.status = :status')
        //    ->setParameter(':status', 'pending')
        //    ->setFirstResult(0)
        //    ->setMaxResults($batchSize);
        //return $qb->execute()->fetchAll();
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
            ->set('t.updated', ':updated')
            ->set('t.processorId', ':processorId')
            ->setParameter(':status', 'processed')
            ->setParameter(':updated', (new DateTimeImmutable())->format('Y-m-d H:i:s'))
            ->setParameter(':processorId', $processorId)
            ->where($qb->expr()->in('id', $ids));
        $qb->execute();
    }
}
