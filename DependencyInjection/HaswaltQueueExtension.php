<?php

namespace Haswalt\QueueBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class HaswaltQueueExtension extends ConfigurableExtension
{
    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $this->createConnections($mergedConfig['connections'], $container);
        $this->createExchanges($mergedConfig['exchanges'], $container);
        $this->createQueues($mergedConfig['queues'], $container);
    }

    private function createConnections(array $config, ContainerBuilder $container)
    {
        foreach ($config as $key => $connectionConfig) {
            $id = strtolower($key);

            $connectionDefinition = new Definition('PhpAmqpLib\Connection\AMQPStreamConnection', [
                $connectionConfig['host'],
                $connectionConfig['port'],
                $connectionConfig['user'],
                $connectionConfig['password'],
                $connectionConfig['vhost'],
            ]);
            $connectionDefinition->setPublic(false);
            $connectionDefinition->setLazy(true);

            $connectionId = sprintf("haswalt_queue.connection.%s", $id);
            $container->setDefinition($connectionId, $connectionDefinition);

            $channelDefinition = new Definition('PhpAmqpLib\Channel\AMQPChannel', [
                new Reference($connectionId)
            ]);
            $channelDefinition->setPublic(true);
            $channelDefinition->setLazy(true);
            $channelDefinition->addTag('queue.channel');

            $channelId = sprintf("haswalt_queue.channel.%s", $id);
            $container->setDefinition($channelId, $channelDefinition);
        }
    }

    private function createExchanges(array $config, ContainerBuilder $container)
    {
        foreach ($config as $key => $exchangeConfig) {
            $connectionId = strtolower($exchangeConfig['connection']);
            $connectionName = sprintf("haswalt_queue.channel.%s", $connectionId);
            $connection = $container->getDefinition($connectionName);
            $connection->addMethodCall('exchange_declare', [
                $key,
                $exchangeConfig['type'],
                $exchangeConfig['passive'],
                $exchangeConfig['durable'],
                $exchangeConfig['auto_delete'],
            ]);
        }
    }

    private function createQueues(array $config, ContainerBuilder $container)
    {
        foreach ($config as $key => $queueConfig) {
            $connectionId = strtolower($queueConfig['connection']);
            $connectionName = sprintf("haswalt_queue.channel.%s", $connectionId);
            $connection = $container->getDefinition($connectionName);
            $connection->addMethodCall('queue_declare', [
                $key,
                $queueConfig['passive'],
                $queueConfig['durable'],
                $queueConfig['exclusive'],
                $queueConfig['auto_delete'],
            ]);

            // now bind to exchange
            $connection->addMethodCall('queue_bind', [
                $key,
                $queueConfig['exchange'],
                $queueConfig['routing_key'],
            ]);
        }
    }
}
