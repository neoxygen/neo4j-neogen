<?php

namespace Neoxygen\Neogen\Processor;

use Faker\Factory;
use Neoxygen\Neogen\Exception\SchemaDefinitionException as SchemaException,
    Neoxygen\Neogen\Processor\VertEdgeProcessor,
    Neoxygen\Neogen\Graph\Graph,
    Neoxygen\Neogen\FakerProvider\Faker;


class PropertyProcessor
{

    private $faker;

    private $graph;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    public function process(VertEdgeProcessor $vertEdge)
    {
        $this->graph = new Graph();

        foreach ($vertEdge->getNodes() as $node) {
            $this->addNodeProperties($node);
        }

        foreach ($vertEdge->getEdges() as $edge) {
            $this->addEdgeProperties($edge);
        }

        return $this->graph;
    }

    public function addNodeProperties(array $vertedge)
    {
        $props = [];
        foreach ($vertedge['properties'] as $key => $property) {
            $value = $this->faker->generate($property->getProvider(), $property->getArguments());
            $props[$property->getName()] = $value;
        }
        $vertedge['properties'] = $props;
        $this->graph->setNode($vertedge);
    }

    public function addEdgeProperties(array $vertedge)
    {
        try {
            $props = [];
            foreach ($vertedge['properties'] as $key => $property) {
                $value = $this->faker->generate($property->getProvider(), $property->getArguments());
                $props[$property->getName()] = $value;
            }
            $vertedge['properties'] = $props;
            $this->graph->setEdge($vertedge);
        } catch (\InvalidArgumentException $e) {
            $msg = $e->getMessage();
            preg_match('/((?:")(.*)(?:"))/', $msg, $output);
            if (isset($output[2])) {
                $msg = sprintf('The faker type "%s" is not defined', $output[2]);
            }
            throw new SchemaException($msg);
        }

    }
}
