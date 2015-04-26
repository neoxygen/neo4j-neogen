<?php

namespace Neoxygen\Neogen\GraphGenerator;

use Neoxygen\Neogen\Schema\GraphSchema;
use Neoxygen\Neogen\Processor\GraphProcessor;

class Generator
{
    protected $graphProcessor;

    public function __construct(GraphProcessor $graphProcessor)
    {
        $this->graphProcessor = $graphProcessor;
    }

    public function generateGraph(GraphSchema $graphSchema)
    {
        /**
         * <-- Here should come the model manager adding properties to the schema
         */

        $graph = $this->graphProcessor->process($graphSchema);
        return $graph;
    }
}
