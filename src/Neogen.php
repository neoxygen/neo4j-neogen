<?php

namespace Neoxygen\Neogen;

use Neoxygen\Neogen\Parser\YamlFileParser,
    Neoxygen\Neogen\Parser\CypherPattern;
use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface;
use Neoxygen\Neogen\DependencyInjection\NeogenExtension;

class Neogen
{
    public static $version = '1.0.0';

    /**
     * @var ContainerBuilder|ContainerInterface
     */
    protected $serviceContainer;

    /**
     * @var array User defined configuration array
     */
    protected $configuration = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        if (null === $container) {
            $container = new ContainerBuilder();
        }

        $this->serviceContainer = $container;

        return $this;
    }

    /**
     * @return Neogen
     */
    public static function create()
    {
        return new self();
    }

    public static function getVersion()
    {
        return self::$version;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return \Neoxygen\Neogen\Neogen
     */
    public function build()
    {
        $extension = new NeogenExtension();
        $this->serviceContainer->registerExtension($extension);
        $this->serviceContainer->loadFromExtension($extension->getAlias(), $this->getConfiguration());
        $this->serviceContainer->compile();
        $this->getParserManager()->registerParser(new YamlFileParser());
        $this->getParserManager()->registerParser(new CypherPattern());

        return $this;
    }

    public function generateGraph(array $userSchema)
    {
        $graphSchema = $this->getSchemaBuilder()->buildGraph($userSchema);

        return $this->getGraphGenerator()->generateGraph($graphSchema);
    }

    /**
     * @return ContainerBuilder|ContainerInterface
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * @return \Neoxygen\Neogen\Parser\ParserManager
     */
    public function getParserManager()
    {
        return $this->getService('neogen.parser_manager');
    }

    /**
     * @return \Neoxygen\Neogen\Schema\GraphSchemaBuilder
     */
    public function getSchemaBuilder()
    {
        return $this->getService('neogen.schema_builder');
    }

    /**
     * @return \Neoxygen\Neogen\GraphGenerator\Generator
     */
    public function getGraphGenerator()
    {
        return $this->getService('neogen.graph_generator');
    }

    /**
     * @param $id The service id
     * @return object
     */
    private function getService($id)
    {
        if (!$this->serviceContainer->isFrozen()) {
            throw new \RuntimeException(sprintf('The Service "%s" can not be accessed. Maybe you forgot to call the "build" method?', $id));
        }

        return $this->serviceContainer->get($id);
    }
}
