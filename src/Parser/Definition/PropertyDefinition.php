<?php

namespace GraphAware\Neogen\Parser\Definition;

class PropertyDefinition
{
    protected $key;

    protected $generator;

    protected $indexed;

    protected $unique;

    public function __construct($key, $generator, $indexed = false, $unique = false)
    {
        $this->key = $key;
        $this->generator = $generator;
        $this->indexed = $indexed;
        $this->unique = $unique;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getGenerator()
    {
        return $this->generator;
    }

    public function isIndexed()
    {
        return $this->indexed;
    }

    public function isUnique()
    {
        return $this->unique;
    }
}