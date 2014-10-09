<?php

namespace Neoxygen\Neogen\Generator;

use Neoxygen\Neogen\Processor\VertEdgeProcessor,
    Neoxygen\Neogen\Processor\PropertyProcessor,
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

    public function generate(array $schema)
    {
        $graph = new Graph();
        $vertEdge = $this->vertEdgeProcessor->process($schema);
        $this->propertyProcessor->process($vertEdge, $graph);

        return $graph;
    }
}