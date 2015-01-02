<?php

namespace Neoxygen\Neogen\GraphGenerator;

use Neoxygen\Neogen\Processor\PropertyProcessor;
use Neoxygen\Neogen\Schema\GraphSchema;
use Neoxygen\Neogen\Processor\VertEdgeProcessor;

class Generator
{
    protected $vertedgeProcessor;

    protected $propertyProcessor;

    public function __construct()
    {
        $this->vertedgeProcessor = new VertEdgeProcessor();
        $this->propertyProcessor = new PropertyProcessor();
    }

    public function generateGraph(GraphSchema $graphSchema)
    {
        /**
         * <-- Here should come the model manager adding properties to the schema
         */

        $vE = $this->vertedgeProcessor->process($graphSchema);
        $graph = $this->propertyProcessor->process($vE, $vE->getGraph());

        print_r($graph);
    }
}