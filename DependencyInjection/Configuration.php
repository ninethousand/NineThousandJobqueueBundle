<?php

namespace NineThousand\Bundle\NineThousandJobqueueBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nine_thousand_jobqueue', 'array');

        $rootNode
            ->children()
                ->arrayNode('control')
                    //->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('NineThousand\\Jobqueue\\Service\\JobqueueControl')->end()
                    ->end()
                ->end()
                ->arrayNode('job')
                    //->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('NineThousand\\Jobqueue\\Job\\StandardJob')->end()
                    ->end()
                ->end()
                ->arrayNode('adapter')
                    //->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('class')->defaultValue('NineThousand\\Jobqueue\\Vendor\\Doctrine\\Adapter\\Queue\\DoctrineQueueAdapter')->end()
                        ->arrayNode('options')
                            //->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('job_entity_class')->defaultValue('NineThousand\\Bundle\\NineThousandJobqueueBundle\\Entity\\Job')->end()
                                ->scalarNode('job_adapter_class')->defaultValue('NineThousand\\Bundle\\NineThousandJobqueueBundle\\Vendor\\Doctrine\\Adapter\\Job\\Symfony2DoctrineJobAdapter')->end()
                                ->scalarNode('history_entity_class')->defaultValue('NineThousand\\Bundle\\NineThousandJobqueueBundle\\Entity\\History')->end()
                                ->scalarNode('history_adapter_class')->defaultValue('NineThousand\\Jobqueue\\Vendor\\Doctrine\\Adapter\\History\\DoctrineHistoryAdapter')->end()
                                ->scalarNode('log_adapter_class')->defaultValue('NineThousand\\Jobqueue\\Vendor\\Doctrine\\Adapter\\Log\\MonologAdapter')->end()
                                ->arrayNode('jobcontrol')
                                    //->addDefaultsIfNotSet()
                                    ->children()
                                        ->arrayNode('type_mapping')
                                            //->addDefaultsIfNotSet()
                                            ->children()
                                                ->scalarNode('SymfonyConsoleJobControl')->defaultValue('NineThousand\\Jobqueue\\Vendor\\Symfony2\\Adapter\\Job\\Control\\Symfony2ConsoleJobControl')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('ui')
                    //->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('pagination')
                            //->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('limit')->defaultValue('40')->end()
                                ->scalarNode('pages_before')->defaultValue('5')->end()
                                ->scalarNode('pages_after')->defaultValue('5')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        return $treeBuilder;
    }
}

