<?php

namespace Neoxygen\Neogen;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface;
use Neoxygen\Neogen\DependencyInjection\NeogenExtension;

class NeogenBuilder
{
    protected $serviceContainer;

    protected $configuration = [];

    public function __construct(ContainerInterface $container = null)
    {
        if (null === $container) {
            $container = new ContainerBuilder();
        }

        $this->serviceContainer = $container;

        return $this;
    }

    public static function create()
    {
        return new self();
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function build()
    {
        $extension = new NeogenExtension();
        $this->serviceContainer->registerExtension($extension);
        $this->serviceContainer->loadFromExtension($extension->getAlias(), $this->getConfiguration());
        $this->serviceContainer->compile();

        $neogen = new Neogen();

        return $neogen;
    }

    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }
}
