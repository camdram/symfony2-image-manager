<?php

namespace Hoyes\ImageManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Doctrine\ORM\Query\Parameter;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HoyesImageManagerExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('hoyes_image_manager.uploadify.token', $config['token']);
        $container->setParameter('hoyes_image_manager.post_route', $config['post_route']);
        $container->setParameter('hoyes_image_manager.data_dir', $config['data_path']);
        $container->setParameter('hoyes_image_manager.max_width', $config['max_width']);
        $container->setParameter('hoyes_image_manager.max_height', $config['max_height']);

        $cacheDir = $container->getParameterBag()->resolveValue($config['cache_path']);
        if (!is_dir($cacheDir)) {
            if (false === @mkdir($cacheDir, 0777, true)) {
                throw new RuntimeException(sprintf('Could not create cache directory "%s".', $cacheDir));
            }
        }
        $container->setParameter('hoyes_image_manager.cache_dir', $cacheDir);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['backend'])) {
            $container->getDefinition('hoyes_image_manager.imagine')
                ->setClass('%imagine.'.$config['backend'].'.class%');
        }
    }
}
