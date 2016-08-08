<?php

namespace App\ConcurrentBatchProcessing;

use Closure;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;

class RunCommand extends BaseCommand
{
    /**
     * @var Processor
     */
    private $processor;


    /**
     * @param Processor $processor
     * @throws LogicException
     */
    public function __construct(Processor $processor)
    {
        parent::__construct();
        $this->processor = $processor;
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('processor:run')
            ->setDescription('This command processes batches of stuff.')
            ->addArgument('processId', InputArgument::REQUIRED, 'The id of the process.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null|int
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processId = $input->getArgument('processId');
        $output->writeln('Start processing data set ...');
        $this->processor->process($processId, $this->getProgressIndicatorFn($output, $processId));
        $output->writeln('Data set processing finished.');
    }

    /**
     * @param OutputInterface $output
     * @param int $processId
     * @return Closure
     */
    private function getProgressIndicatorFn(OutputInterface $output, $processId)
    {
        return function ($batchNumber) use ($output, $processId) {
            $output->writeln(sprintf('Batch %s is processed by processor %s', $batchNumber, $processId));
        };
    }
}