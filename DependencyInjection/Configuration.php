<?php

namespace Hoyes\ImageManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hoyes_image_manager');

        $rootNode
            ->children()
            ->scalarNode('post_route')->defaultValue('hoyes_image_manager_post')->end()
            ->scalarNode('token')->defaultValue('my_secret_token')->end()
            ->scalarNode('data_path')->defaultValue('%kernel.root_dir%/data')->end()
            ->scalarNode('cache_path')->defaultValue('%kernel.cache_dir%/hoyes_image_manager')->end()
            ->scalarNode('backend')->end()
            ->scalarNode('max_width')->defaultValue('1024')->end()
            ->scalarNode('max_height')->defaultValue('768')->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
