<?php

namespace Neoxygen\Neogen;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface;
use Neoxygen\Neogen\DependencyInjection\NeogenExtension;

class Builder
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

    public function setSeed($arg)
    {
        if (null !== $arg) {
            $v = (int) $arg;
            $this->configuration['seed'] = $v;
        }

        return $this;
    }

    public function getSeed()
    {
        if (!array_key_exists('seed', $this->configuration)) {

            return null;
        }

        return $this->configuration['seed'];
    }
}
