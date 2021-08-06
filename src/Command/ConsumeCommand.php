<?php

namespace Radish\RadishBundle\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct('radish:consume');
        $this->container = $container;
    }

    public function configure()
    {
        $this->addArgument('consumer', InputArgument::REQUIRED, 'The name of the consumer to consume');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $consumerName = $input->getArgument('consumer');
        $this->container->get(sprintf('radish.consumer.%s', $consumerName))->consume();

        return 0;
    }
}
