<?php

namespace GraphAware\Neogen\Parser\Definition;

class NodeDefinition
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var array
     */
    protected $labels = [];

    /**
     * @var array
     */
    protected $models = [];

    /**
     * @var \GraphAware\Neogen\Parser\Definition\PropertyDefinition[]
     */
    protected $properties = [];

    /**
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = (string) $identifier;
    }

    /**
     * @param string $label
     */
    public function addLabel($label)
    {
        $this->labels[] = $label;
    }

    /**
     * @param string $label
     * @return bool
     */
    public function hasLabel($label)
    {
        return in_array($label, $this->labels);
    }

    /**
     * @param string $model
     */
    public function addModel($model)
    {
        if (null === $model) { return; }
        $this->models[] = $model;
    }

    /**
     * @param \GraphAware\Neogen\Parser\Definition\PropertyDefinition $propertyDefinition
     */
    public function addProperty(PropertyDefinition $propertyDefinition)
    {
        $this->properties[] = $propertyDefinition;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasProperty($key)
    {
        foreach ($this->properties as $property) {
            if ($key === $property->getKey()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return array
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * @return \GraphAware\Neogen\Parser\Definition\PropertyDefinition[]
     */
    public function getProperties()
    {
        return $this->properties;
    }


}