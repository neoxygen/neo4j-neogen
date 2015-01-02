<?php

namespace Neoxygen\Neogen;

use Neoxygen\Neogen\Parser\YamlFileParser;
use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface;
use Neoxygen\Neogen\DependencyInjection\NeogenExtension;

class Neogen
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
        $this->getParserManager()->registerParser(new YamlFileParser());

        return $this;
    }

    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    public function getParserManager()
    {
        return $this->getService('neogen.parser_manager');
    }

    public function getGraphGenerator()
    {
        return $this->getService('neogen.graph_generator');
    }

    private function getService($id)
    {
        if (!$this->serviceContainer->isFrozen()) {
            throw new \RuntimeException(sprintf('The Service "%s" can not be accessed. Maybe you forgot to call the "build" method?', $id));
        }

        return $this->serviceContainer->get($id);
    }
}
