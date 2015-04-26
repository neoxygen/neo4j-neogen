<?php

namespace Neoxygen\Neogen\Graph;

use Neoxygen\Neogen\Schema\GraphSchema;
use Neoxygen\Neogen\Util\ObjectCollection;

class Graph
{
    private $nodes;

    private $edges;

    private $schema;

    public function getEdges()
    {
        return $this->edges;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function setNodes(ObjectCollection $nodes)
    {
        $this->nodes = $nodes;
    }

    public function setEdges(ObjectCollection $edges)
    {
        $this->edges = $edges;
    }

    public function setSchema(GraphSchema $schema)
    {
        $this->schema = $schema;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function toArray()
    {
        return array(
            'nodes' => $this->nodes->getValues(),
            'edges' => $this->edges->getValues(),
            'schema' => $this->getSchema()->toArray()
        );
    }
}
