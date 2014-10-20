<?php

namespace Neoxygen\Neogen\Generator;

use Neoxygen\Neogen\Processor\VertEdgeProcessor,
    Neoxygen\Neogen\Processor\PropertyProcessor,
    Neoxygen\Neogen\Schema\GraphSchemaDefinition,
    Neoxygen\Neogen\Graph\Graph;

class GraphGenerator
{
    private $vertEdgeProcessor;

    private $propertyProcessor;

    public function __construct()
    {
        $this->vertEdgeProcessor = new VertEdgeProcessor();
        $this->propertyProcessor = new PropertyProcessor();
    }

    public function generate(GraphSchemaDefinition $schema)
    {
        $graph = new Graph();
        $vertEdge = $this->vertEdgeProcessor->process($schema);
        $this->propertyProcessor->process($vertEdge, $graph);

        return $graph;
    }
}