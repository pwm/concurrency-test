<?php

namespace App\ConcurrentBatchProcessing;

use Closure;
use Exception;
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
            ->addArgument('processorId', InputArgument::REQUIRED, 'The id of the process.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null|int
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processorId = $input->getArgument('processorId');
        $output->writeln('<info>'.sprintf('Processor %s start processing ...', $processorId).'<info>');
        usleep(10000);

        $startTime = microtime(true);
        $this->processor->process(
            $processorId, 
            $this->getProgressIndicatorFn($output, $processorId),
            $this->getFailureIndicatorFn($output, $processorId)
        );
        $endTime = microtime(true) - $startTime;

        usleep(10000);
        $output->writeln('<info>'.sprintf('Processor %s finished.', $processorId).'<info>');
        $output->writeln(sprintf('Total processing: %s', $endTime));
    }

    /**
     * @param OutputInterface $output
     * @param int $processorId
     * @return Closure
     */
    private function getProgressIndicatorFn(OutputInterface $output, $processorId)
    {
        return function ($msg) use ($output, $processorId) {
            $txt = '<info>'.sprintf('Processor %s reports: ', $processorId).$msg.'</info>';
            $output->writeln($txt);
        };
    }

    /**
     * @param OutputInterface $output
     * @param int $processorId
     * @return Closure
     */
    private function getFailureIndicatorFn(OutputInterface $output, $processorId)
    {
        return function ($msg) use ($output, $processorId) {
            $txt = '<error>'.sprintf('Processor %s reports: ', $processorId).$msg.'</error>';
            $output->writeln($txt);
        };
    }
}