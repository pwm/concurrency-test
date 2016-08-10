<?php

namespace App\Infrastructure;

use DateTimeImmutable;
use Doctrine\DBAL\DBALException;
use PDO;

class StuffRepository extends BaseRepository
{
    /**
     * @return int
     */
    public function getNumberOfRecords()
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->select('*')
            ->from('stuff');
        return $qb->execute()->rowCount();
    }

    /**
     * @param int $batchNumber
     * @throws DBALException
     */
    public function acquireBatch($batchNumber)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert('batchLock')
            ->values(['batchId' => ':batchId'])
            ->setParameter(':batchId', $batchNumber);
        $qb->execute();
    }

    /**
     * @param int $batchNumber
     * @param int $batchSize
     * @return array
     * @throws DBALException
     */
    public function getBatch($batchNumber, $batchSize)
    {
        // normal, non-locking mode
        // all TRs will read and update => records updated as many times as many TRs => :(
        //$sql = 'select * from stuff where status = :status limit :lFrom, :lTo';

        // shared lock
        // reads, does the work and then first TR wins, the rest fails
        $sql = 'select * from stuff where status = :status limit :lFrom, :lTo LOCK IN SHARE MODE';
        //$sql = 'select * from stuff limit :lFrom, :lTo LOCK IN SHARE MODE';

        // exclusive lock
        // here it will simply wait till another process does thr work and then
        // implicitly reads the next batch
        //$sql = 'select * from stuff where status = :status limit :lFrom, :lTo FOR UPDATE';

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(':status', 'pending');
        $stmt->bindValue(':lFrom', $batchNumber * $batchSize, PDO::PARAM_INT);
        $stmt->bindValue(':lTo', $batchSize, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();

        //@todo: how to add locking to query builder
        //$qb = $this->connection->createQueryBuilder();
        //$qb
        //    ->select('*')
        //    ->from('stuff', 't')
        //    ->where('t.status = :status')
        //    ->setParameter(':status', 'pending')
        //    ->setFirstResult(0)
        //    ->setMaxResults($batchSize);
        //return $qb->execute()->fetchAll();
    }

    /**
     * @param int $pid
     * @param array $ids
     * @return int
     */
    public function updateBatch($pid, array $ids)
    {
        //$this->writeLog($pid);
        
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->update('stuff', 't')
            ->set('t.status', ':status')
            ->set('t.updated', ':updated')
            ->set('t.pid', ':pid')
            ->set('t.timesProcessed', 't.timesProcessed + 1')
            ->setParameter(':status', 'processed')
            ->setParameter(':updated', (new DateTimeImmutable())->format('Y-m-d H:i:s'))
            ->setParameter(':pid', $pid)
            ->where($qb->expr()->in('id', $ids));
        $qb->execute();
    }

    /**
     * @param int $pid
     */
    private function writeLog($pid)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb
            ->insert('log')
            ->values([
                'pid' => ':pid',
                'ts'  => ':ts'
            ])
            ->setParameter(':ts', microtime(true))
            ->setParameter(':pid', $pid);
        $qb->execute();
    }
}
