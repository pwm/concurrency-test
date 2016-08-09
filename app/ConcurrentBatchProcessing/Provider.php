<?php

namespace App\ConcurrentBatchProcessing;

use App\ConcurrentBatchProcessing\Commands\SetupCommand;
use App\ConcurrentBatchProcessing\Commands\RunCommand;
use App\Infrastructure\DbCreator;
use App\Infrastructure\StuffRepository;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\Exception\LogicException;

class Provider implements ServiceProviderInterface
{
    /**
     * @param Container $container
     * @throws LogicException
     */
    public function register(Container $container)
    {
        $container[SetupCommand::class] = new SetupCommand(function ($numberOfRows) use ($container) {
            $dbCreator = $container[DbCreator::class]; /** @var DbCreator $dbCreator */
            $dbCreator->setNumberOfRows($numberOfRows);
            return $dbCreator;
        });
        
        $processor = new Processor($container[StuffRepository::class]);
        $container[RunCommand::class] = new RunCommand($processor);
    }
}