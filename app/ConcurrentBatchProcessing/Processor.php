<?php

namespace App\ConcurrentBatchProcessing;

use App\Infrastructure\StuffRepository;
use Closure;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\DriverException;
use Exception;

class Processor
{
    /**
     * @var int
     */
    private static $batchSize = 10;

    /**
     * @var int
     */
    //private static $simulatedBatchProcessTimeInMicroSeconds = 500000; // 0.5 sec
    private static $simulatedBatchProcessTimeInMicroSeconds = 5000000; // 5 sec
    //private static $simulatedBatchProcessTimeInMicroSeconds = 100000000; // 100 sec

    /**
     * @var StuffRepository $stuffRepository
     */
    private $stuffRepository;

    /**
     * @var int
     */
    private $step = 1;


    /**
     * @param StuffRepository $stuffRepository
     */
    public function __construct(StuffRepository $stuffRepository)
    {
        $this->stuffRepository = $stuffRepository;
    }

    /**
     * @param int $processId
     * @param Closure $progressCallback
     * @param Closure $failureCallback
     * @throws Exception
     */
    public function process($processId, Closure $progressCallback, Closure $failureCallback)
    {
        $numberOfBatches = ceil($this->stuffRepository->getNumberOfRecords() / self::$batchSize);
        for ($batchNumber = 0; $batchNumber <= $numberOfBatches; $batchNumber++) {
            try {
                $this->stuffRepository->acquireBatch($batchNumber);
                $this->step = 1;
                $this->stuffRepository->tr(function () use ($processId, $progressCallback, $batchNumber) {
                    //$progressCallback(sprintf('batch %s is about to be read ...', $batchNumber));
                    $currentBatch = $this->stuffRepository->getBatch($batchNumber, self::$batchSize);
                    $currentBatchIds = array_map(function ($e) { return $e['id']; }, $currentBatch);
                    $this->step++;
                    if (count($currentBatch) > 0) {
                        $progressCallback(sprintf('batch %s was read: %s About to be processed ...', $batchNumber, implode(',', $currentBatchIds)));
                        $this->simulateProcessingCurrentBatch();
                        $this->step++;
                        //$progressCallback(sprintf('batch %s was processed. About to be updated ...', $batchNumber));
                        $this->stuffRepository->updateBatch($processId, $currentBatchIds);
                        $progressCallback(sprintf('batch %s has been updated.', $batchNumber));
                    }
                });
            } catch (DriverException $e) {
                $failureCallback(sprintf('batch %s failed at step %s.', $batchNumber, $this->step));
            }
        }
    }

    /**
     * @return void
     */
    private function simulateProcessingCurrentBatch()
    {
        usleep(self::$simulatedBatchProcessTimeInMicroSeconds);
    }
}
