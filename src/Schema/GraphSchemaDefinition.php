<?php

namespace Neoxygen\Neogen\Schema;

class GraphSchemaDefinition
{
    private $nodes;

    private $edges;

    public function setEdge(array $edgeDefinition)
    {
        $this->edges[] = $edgeDefinition;
    }

    public function setNode(array $nodeDefinition)
    {
        $this->nodes[] = $nodeDefinition;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getEdges()
    {
        return $this->edges;
    }

    public function setNodes(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public function setEdges(array $edges)
    {
        $this->edges = $edges;
    }

    public function getSchema()
    {
        return [
            'nodes' => $this->getNodes(),
            'edges' => $this->getEdges()
        ];
    }
}