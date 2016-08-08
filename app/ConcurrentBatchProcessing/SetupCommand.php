<?php

namespace App\ConcurrentBatchProcessing;

use Closure;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;

class SetupCommand extends BaseCommand
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
            ->setName('processor:setup')
            ->setDescription('This command sets up the stuff table with dummy data.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Recreating the stuff table and adding dummy data ...');
        $this->processor->setUp();
        $output->writeln('Done.');
    }
}