<?php
/**
 * Copyright: IMPER.INFO Adrian Szuszkiewicz
 * Date: 26.09.2019
 * Time: 19:19
 */

namespace Imper86\SupervisorBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('imper86_supervisor');

        $treeBuilder->getRootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('workspace_directory')->defaultValue('%kernel.project_dir%/var/imper86supervisor/%kernel.environment%')->end()
                ->arrayNode('instances')
                    ->useAttributeAsKey('name', false)
                    ->beforeNormalization()
                        ->always()
                        ->then(function ($v) {
                            foreach ($v as $instanceName => $config) {
                                if (!isset($config['name'])) {
                                    $v[$instanceName]['name'] = $instanceName;
                                }
                            }

                            return $v;
                        })
                    ->end()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('commands')
                                ->useAttributeAsKey('worker_name', false)
                                ->beforeNormalization()
                                    ->always()
                                    ->then(function ($v) {
                                        foreach ($v as $workerName => $config) {
                                            if (!isset($config['worker_name'])) {
                                                $v[$workerName]['worker_name'] = $workerName;
                                            }
                                        }

                                        return $v;
                                    })
                                ->end()
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('command')->isRequired()->cannotBeEmpty()->end()
                                        ->scalarNode('worker_name')->isRequired()->cannotBeEmpty()->end()
                                        ->integerNode('numprocs')->defaultValue(1)->end()
                                        ->integerNode('startsecs')->defaultValue(0)->end()
                                        ->booleanNode('autorestart')->defaultTrue()->end()
                                        ->scalarNode('stopsignal')->defaultValue('INT')->end()
                                        ->booleanNode('stopasgroup')->defaultTrue()->end()
                                        ->integerNode('stopwaitsecs')->defaultValue(60)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

}
