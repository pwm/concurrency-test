<?php

namespace App\ConcurrentBatchProcessing;

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
        $processor = new Processor($container[StuffRepository::class]);
        $container[SetupCommand::class] = new SetupCommand($processor);
        $container[RunCommand::class] = new RunCommand($processor);
    }
}