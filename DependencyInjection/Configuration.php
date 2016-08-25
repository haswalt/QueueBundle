<?php

namespace Haswalt\QueueBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('haswalt_queue');

        $this->addConnections($rootNode);
        $this->addExchanges($rootNode);
        $this->addQueues($rootNode);

        return $treeBuilder;
    }

    private function addConnections(NodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('connection')
            ->children()
                ->arrayNode('connections')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->defaultValue('localhost')->end()
                            ->scalarNode('port')->defaultValue(5672)->end()
                            ->scalarNode('user')->defaultValue('guest')->end()
                            ->scalarNode('password')->defaultValue('guest')->end()
                            ->scalarNode('vhost')->defaultValue('/')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addExchanges(NodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('exchange')
            ->children()
                ->arrayNode('exchanges')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('connection')->defaultValue('default')->end()
                            ->scalarNode('type')->defaultValue('direct')->end()
                            ->booleanNode('passive')->defaultValue(false)->end()
                            ->booleanNode('durable')->defaultValue(true)->end()
                            ->booleanNode('auto_delete')->defaultValue(false)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addQueues(NodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('queue')
            ->children()
                ->arrayNode('queues')
                    ->useAttributeAsKey('key')
                    ->prototype('array')
                        ->children()
                        ->scalarNode('connection')->defaultValue('default')->end()
                        ->scalarNode('exchange')->isRequired(true)->end()
                        ->booleanNode('passive')->defaultValue(false)->end()
                        ->booleanNode('durable')->defaultValue(true)->end()
                        ->booleanNode('exclusive')->defaultValue(false)->end()
                        ->booleanNode('auto_delete')->defaultValue(false)->end()
                        ->scalarNode('routing_key')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
