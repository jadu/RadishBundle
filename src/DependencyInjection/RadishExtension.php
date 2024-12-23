<?php

namespace Radish\RadishBundle\DependencyInjection;

use Radish\Broker\AMQPFactory;
use Radish\Broker\Connection;
use Radish\Broker\ExchangeRegistry;
use Radish\Broker\QueueRegistry;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RadishExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $this->loadConnection($config['connection'], $container);

        $container->getDefinition(ExchangeRegistry::class)->setArguments([
            new Reference(Connection::class),
            $config['exchanges']
        ]);

        $container->getDefinition(QueueRegistry::class)->setArguments([
            new Reference(Connection::class),
            $config['queues']
        ]);

        $container->setParameter('radish.consumers', $config['consumers']);

        foreach ($config['consumers'] as $name => $consumer) {
            $this->loadConsumer($name, $consumer, $container);
        }
        foreach ($config['pollers'] as $name => $consumer) {
            $this->loadPoller($name, $consumer, $container);
        }

        foreach ($config['producers'] as $name => $producer) {
            $this->loadProducer($name, $producer, $container);
        }
    }

    private function loadConnection(array $connection, ContainerBuilder $container)
    {
        $definition = new Definition(Connection::class);
        $definition->setArguments([
            new Reference(AMQPFactory::class),
            $connection
        ]);

        $container->setDefinition(Connection::class, $definition);
    }

    private function loadConsumer($name, array $consumer, ContainerBuilder $container)
    {
        $workers = [];
        foreach ($consumer['queues'] as $queueName => $queue) {
            $workers[$queueName] = new Reference($queue['worker']);
        }

        $definition = new ChildDefinition('radish.consumer');

        $args = [
            array_keys($consumer['queues']),
            $consumer['middleware'],
            $workers
        ];

        $definition->setArguments($args);
        $definition->addTag('radish.consumer', ['key' => sprintf('radish.consumer.%s', $name)]);

        $container->setDefinition(sprintf('radish.consumer.%s', $name), $definition);
    }

    public function loadPoller($name, array $poller, ContainerBuilder $container)
    {
        $workers = [];
        foreach ($poller['queues'] as $queueName => $queue) {
            $workers[$queueName] = new Reference($queue['worker']);
        }

        $definition = new ChildDefinition('radish.poller');

        $args = [
            array_keys($poller['queues']),
            $poller['middleware'],
            $workers,
            $poller['interval']
        ];

        $definition->setArguments($args);
        $definition->addTag('radish.poller', ['key' => sprintf('radish.poller.%s', $name)]);

        $container->setDefinition(sprintf('radish.poller.%s', $name), $definition);
    }

    private function loadProducer($name, array $producer, ContainerBuilder $container)
    {
        $definition = new ChildDefinition('radish.producer');
        $definition->setArguments([
            $producer['exchange']
        ]);

        $container->setDefinition(sprintf('radish.producer.%s', $name), $definition);
    }
}
