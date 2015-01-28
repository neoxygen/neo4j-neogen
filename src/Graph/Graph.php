<?php

namespace Neoxygen\Neogen\Graph;

class Graph
{
    private $nodes = [];

    private $edges = [];

    public function getEdges()
    {
        return $this->edges;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function setEdge(array $edge)
    {
        $this->edges[] = $edge;
    }

    public function setNode(array $node)
    {
        $this->nodes[] = $node;
    }

    public function getNodesCount()
    {
        return count($this->nodes);
    }

    public function getEdgesCount()
    {
        return count($this->edges);
    }

    public function setEdges(array $edges)
    {
        $this->edges = $edges;
    }
}
