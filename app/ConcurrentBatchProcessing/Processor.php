<?php

namespace App\ConcurrentBatchProcessing;

use App\Infrastructure\StuffRepository;
use Closure;
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
    private static $simulatedProcessTimePerBatch = 2;

    /**
     * @var StuffRepository $stuffRepository
     */
    private $stuffRepository;


    /**
     * @param StuffRepository $stuffRepository
     */
    public function __construct(StuffRepository $stuffRepository)
    {
        $this->stuffRepository = $stuffRepository;
    }

    /**
     * 
     */
    public function setUp()
    {
        $this->stuffRepository->createTable();
        $this->stuffRepository->seedDummyData();
    }

    /**
     * @param int $processId
     * @param Closure $progressCallback
     * @throws Exception
     */
    public function process($processId, Closure $progressCallback)
    {
        $numberOfBatches = ceil($this->stuffRepository->getNumberOfStuff() / self::$batchSize);
        for ($i = 0; $i < $numberOfBatches; $i++) {
            $this->stuffRepository->tr(function () use ($processId, $progressCallback, $i) {
                $currentBatch = $this->getCurrentBatch();
                $this->simulateProcessingCurrentBatch();
                $this->updateCurrentBatch($processId, array_map(function ($e) { return $e['id']; }, $currentBatch));
                $progressCallback($i + 1);
            });
        }
    }

    /**
     * @return array
     */
    private function getCurrentBatch()
    {
        return $this->stuffRepository->getBatch(self::$batchSize);
    }

    /**
     * @return void
     */
    private function simulateProcessingCurrentBatch()
    {
        sleep(self::$simulatedProcessTimePerBatch);
    }

    /**
     * @param int $processId
     * @param array $ids
     * @return void
     */
    private function updateCurrentBatch($processId, array $ids)
    {
        $this->stuffRepository->updateBatch($processId, $ids);
    }
}
