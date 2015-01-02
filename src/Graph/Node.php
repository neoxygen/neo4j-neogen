<?php

namespace Neoxygen\Neogen\Graph;

use Neoxygen\Neogen\Util\ObjectCollection;

class Node
{
    protected $id;

    protected $properties = [];

    protected $labels = [];

    public function __construct($id)
    {
        if (null === $id) {
            throw new \InvalidArgumentException('The ID on node construction can not be null');
        }

        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }



    public function getProperties()
    {
        return $this->properties;
    }

    public function addProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }


    public function hasProperty($name)
    {
        if (array_key_exists($name, $this->properties)) {
            return true;
        }

        return false;
    }

    public function hasProperties()
    {
        if (!empty($this->properties)) {
            return true;
        }

        return false;
    }

    public function getLabels()
    {
        if (empty($this->labels)) {
            throw new \RuntimeException('The node has no label');
        }
        return $this->labels;
    }

    public function addLabels(array $labels)
    {
        $this->labels = $labels;
    }

    public function getLabel()
    {
        if (!empty($this->labels)) {
            return $this->labels[0];
        }

        throw new \RuntimeException('The node has no label');
    }
}
