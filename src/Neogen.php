<?php

namespace Neoxygen\Neogen;

use Neoxygen\Neogen\Parser\YamlFileParser,
    Neoxygen\Neogen\Parser\CypherPattern;
use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\ContainerInterface;
use Neoxygen\Neogen\DependencyInjection\NeogenExtension;

class Neogen
{
    /**
     * @var string
     */
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
     * Creates a new instance of Neogen
     *
     * @return Neogen
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Returns the Neogen library's version
     *
     * @return string Neogen version
     */
    public static function getVersion()
    {
        return self::$version;
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

    /**
     * Generates a graph based on a given user schema array
     *
     * @param array $userSchema
     * @return Graph\Graph
     */
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
     * Returns the parser manager service
     *
     * @return \Neoxygen\Neogen\Parser\ParserManager
     */
    public function getParserManager()
    {
        return $this->getService('neogen.parser_manager');
    }

    /**
     * @param string $parser
     * @return Parser\ParserInterface
     * @throws Exception\ParserNotFoundException
     */
    public function getParser($parser)
    {
        return $this->getParserManager()->getParser($parser);
    }

    /**
     * Returns the graph serializer service
     *
     * @return \Neoxygen\Neogen\Util\GraphSerializer
     */
    public function getGraphSerializer()
    {
        return $this->getService('neogen.graph_serializer');
    }

    /**
     * Returns the graph generator service
     *
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

    /**
     * Return the current configuration
     *
     * @return array
     */
    private function getConfiguration()
    {
        return $this->configuration;
    }
}
