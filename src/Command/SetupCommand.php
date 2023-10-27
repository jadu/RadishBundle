<?php

namespace Radish\RadishBundle\Command;

use Radish\Broker\ExchangeRegistry;
use Radish\Broker\QueueRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupCommand extends Command
{
    protected $exchangeRegistry;
    protected $queueRegistry;

    public function __construct(ExchangeRegistry $exchangeRegistry, QueueRegistry $queueRegistry)
    {
        $this->exchangeRegistry = $exchangeRegistry;
        $this->queueRegistry = $queueRegistry;

        parent::__construct('radish:setup');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->exchangeRegistry->setUp();
        $this->queueRegistry->setUp();

        return 0;
    }
}
