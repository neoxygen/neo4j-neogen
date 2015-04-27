<?php

namespace Neoxygen\Neogen\Util;

use JMS\Serializer\SerializerBuilder;
use Neoxygen\Neogen\Graph\Graph;

class GraphSerializer
{
    protected $serializer;

    public function __construct()
    {
        $this->serializer = SerializerBuilder::create()
            ->build();
    }

    public function serializeGraphToJson(Graph $graph)
    {
        return $this->serializer->serialize($graph->toArray(), 'json');
    }
}