<?php

namespace GraphAware\Neogen\Parser;

class CypherDefinition
{
    protected $parts;

    protected $nodes;

    protected $edges;

    /**
     * @return mixed
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * @return mixed
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @return mixed
     */
    public function getEdges()
    {
        return $this->edges;
    }


}