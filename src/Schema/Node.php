<?php

namespace Neoxygen\Neogen\Schema;

use Neoxygen\Neogen\Util\ObjectCollection,
    Neoxygen\Neogen\Schema\NodeProperty;

class Node
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var ObjectCollection[string]
     */
    protected $labels;

    /**
     * @var ObjectCollection[\Neoxygen\Neogen\Schema\NodeProperty]
     */
    protected $properties;

    /**
     * @var int amount of nodes to be generated
     */
    protected $amount;

    /**
     * @param $identifier The node identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = (string) $identifier;
        $this->properties = new ObjectCollection();
        $this->labels = new ObjectCollection();
        $this->amount = 1;
    }

    /**
     *
     * Get Node Properties
     *
     * @return ObjectCollection[\Neoxygen\Neogen\Schema\NodeProperty] A collection of properties for this node
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Adds a property to the node
     *
     * @param  NodeProperty $property
     * @return bool
     */
    public function addProperty(NodeProperty $property)
    {
        foreach ($this->properties as $prop) {
            if ($prop->getName() === $property->getName()) {
                $this->properties->removeElement($prop);
            }
        }

        return $this->properties->add($property);
    }

    /**
     * Returns the properties count for this node
     *
     * @return int
     */
    public function getPropertiesCount()
    {
        return $this->properties->count();
    }

    /**
     * Returns whether or not this node has properties
     *
     * @return bool
     */
    public function hasProperties()
    {
        if ($this->properties->isEmpty()) {
            return false;
        }

        return true;
    }

    /**
     * Get all the properties that are indexed
     *
     * @return array
     */
    public function getIndexedProperties()
    {
        $props = [];
        foreach ($this->properties as $property) {
            if ($property->isIndexed()) {
                $props[] = $property;
            }
        }

        return $props;
    }

    /**
     * Get all the properties that are unique
     *
     * @return array
     */
    public function getUniqueProperties()
    {
        $props = [];
        foreach ($this->properties as $property) {
            if ($property->isUnique()) {
                $props[] = $property;
            }
        }

        return $props;
    }

    /**
     * Get the node identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the node labels
     *
     * @return ObjectCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Adds a label to this node, checks if the label does not exist to avoid duplicates
     *
     * @param  string $label
     * @return bool
     */
    public function addLabel($label)
    {
        if (null !== $label) {
            $l = (string) $label;
            if (!$this->hasLabel($l)) {
                $this->labels->add($l);

                return true;
            }
        }

        return false;
    }

    /**
     * Adds multiple labels to this node
     *
     * @param array $labels
     */
    public function addLabels(array $labels)
    {
        foreach ($labels as $label) {
            $this->addLabel($label);
        }
    }

    /**
     * Checks whether or not this node has the specified label
     *
     * @param  string $label
     * @return bool
     */
    public function hasLabel($label)
    {
        if (null !== $label) {
            $l = (string) $label;
            if ($this->labels->contains($l)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether or not the node has a property with the specified name
     *
     * @param $name
     * @return bool
     */
    public function hasProperty($name)
    {
        if (null !== $name) {
            $n = (string) $name;
            foreach ($this->properties as $property) {
                if ($property->getName() === $n) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setAmount($amount)
    {
        if (null !== $amount) {
            $this->amount = (int) $amount;
        }
    }
}
