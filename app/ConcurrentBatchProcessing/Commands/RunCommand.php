<?php

namespace App\ConcurrentBatchProcessing\Commands;

use App\ConcurrentBatchProcessing\Processor;
use Closure;
use Exception;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
     * @var bool
     */
    private $debug;


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
            ->addArgument('pid', InputArgument::REQUIRED, 'The id of the process.')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Run in debug mode');
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
        $this->debug = $input->getOption('debug');
        $pid = $input->getArgument('pid');
        $output->writeln('<info>'.sprintf('Processor %s start processing ...', $pid).'<info>');

        $startTime = microtime(true);
        $this->processor->process(
            $pid, 
            $this->getProgressIndicatorFn($output, $pid),
            $this->getFailureIndicatorFn($output, $pid)
        );
        $endTime = microtime(true) - $startTime;

        $output->writeln('<info>'.sprintf('Processor %s finished.', $pid).'<info>');
        $output->writeln(sprintf('Total processing: %s', $endTime));
    }

    /**
     * @param OutputInterface $output
     * @param int $pid
     * @return Closure
     */
    private function getProgressIndicatorFn(OutputInterface $output, $pid)
    {
        return function ($msg) use ($output, $pid) {
            if ($this->debug) {
                $txt = '<info>'.sprintf('Processor %s reports at %s: ', $pid, microtime(true)).$msg.'</info>';
                $output->writeln($txt);
            }
        };
    }

    /**
     * @param OutputInterface $output
     * @param int $pid
     * @return Closure
     */
    private function getFailureIndicatorFn(OutputInterface $output, $pid)
    {
        return function ($msg) use ($output, $pid) {
            if ($this->debug) {
                $txt = '<error>'.sprintf('Processor %s reports: ', $pid).$msg.'</error>';
                $output->writeln($txt);
            }
        };
    }
}