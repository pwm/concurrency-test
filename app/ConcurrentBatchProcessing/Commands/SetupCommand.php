<?php

namespace App\ConcurrentBatchProcessing\Commands;

use App\Infrastructure\DbCreator;
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
     * @var DbCreator
     */
    private $dbCreator;

    /**
     * @var Closure
     */
    private $dbCreatorResolver;


    /**
     * @param Closure $dbCreatorResolver
     * @throws LogicException
     */
    public function __construct(Closure $dbCreatorResolver)
    {
        parent::__construct();
        $this->dbCreatorResolver = $dbCreatorResolver;
    }

    /**
     * @param DbCreator $dbCreator
     */
    private function setDbCreator(DbCreator $dbCreator)
    {
        $this->dbCreator = $dbCreator;
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this
            ->setName('processor:setup')
            ->setDescription('This command sets up the stuff table with dummy data.')
            ->addArgument('numberOfRows', InputArgument::REQUIRED, 'The number of entries.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null|int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $numberOfRows = $input->getArgument('numberOfRows');
        $this->setDbCreator(call_user_func($this->dbCreatorResolver, $numberOfRows));
        $output->writeln(sprintf('Creating the stuff table and adding %s dummy data ...', $numberOfRows));
        $this->dbCreator->setUp();
        $output->writeln('Done.');
    }
}