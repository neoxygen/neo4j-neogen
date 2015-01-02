<?php

namespace Neoxygen\Neogen\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader,
    Symfony\Component\DependencyInjection\Extension\ExtensionInterface,
    Symfony\Component\DependencyInjection\Definition,
    Symfony\Component\Config\Definition\Processor,
    Symfony\Component\Config\FileLocator;

class NeogenExtension implements ExtensionInterface
{
    protected $container;

    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;
        $processor = new Processor();
        $configuration = new Configuration();

        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');

    }

    public function getAlias()
    {
        return 'neogen';
    }

    public function getXsdValidationBasePath()
    {
        return false;
    }

    public function getNamespace()
    {
        return false;
    }
}
