<?php

namespace Neoxygen\Neogen\Schema;

use Neoxygen\Neogen\Util\ObjectCollection,
    Neoxygen\Neogen\Schema\RelationshipProperty;
use Neoxygen\Neogen\Exception\SchemaDefinitionException;

class Relationship
{
    /**
     * @var string Relationship's start node identifier
     */
    protected $startNode;

    /**
     * @var string Relationship's end node identifier
     */
    protected $endNode;

    /**
     * @var string The relationship TYPE
     */
    protected $type;

    /**
     * @var ObjectCollection[\Neoxygen\Neogen\Schema\RelationshipProperty] A collection of relationship properties
     */
    protected $properties;

    /**
     * @var string Cardinality of the relationshop
     */
    protected $cardinality;

    /**
     * @var null|int User defined percentage of target nodes
     */
    protected $percentage;

    /**
     * @param string $startNode The start node identifier of the relationship
     * @param string $endNode   The end node identifier of the relationship
     * @param string $type      the relationship's type
     */
    public function __construct($startNode, $endNode, $type)
    {
        $this->startNode = (string) $startNode;
        $this->endNode = (string) $endNode;
        $this->type = (string) $type;
        $this->properties = new ObjectCollection();
    }

    /**
     * Returns the start node identifier
     *
     * @return string
     */
    public function getStartNode()
    {
        return $this->startNode;
    }

    /**
     * Returns the end node identifier
     *
     * @return string
     */
    public function getEndNode()
    {
        return $this->endNode;
    }

    /**
     * Returns the relationship type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns a collection of relationship properties objects
     *
     * @return ObjectCollection[\Neoxygen\Neogen\Schema\RelationshipProperty]
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Adds a relationship property to the collection and avoid duplicated
     *
     * @param  RelationshipProperty $property
     * @return bool
     */
    public function addProperty(RelationshipProperty $property)
    {
        foreach ($this->properties as $prop) {
            if ($prop->getName() === $property->getName()) {
                $this->properties->removeElement($prop);
            }
        }

        return $this->properties->add($property);
    }

    /**
     * Returns whether or not this relationship has properties
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
     * Checks whether or not this relationship has the property with the specified name
     *
     * @param  string $name The relationship property name
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

    public function getCardinality()
    {
        return $this->cardinality;
    }

    public function setCardinality($v)
    {
        if (null !== $v) {
            $this->cardinality = (string) $v;
        }
    }

    public function getPercentage()
    {
        return $this->percentage;
    }

    public function hasPercentage()
    {
        return null !== $this->percentage;
    }

    public function setPercentage($percentage)
    {
        $pct = (int) $percentage;
        if (0 === $pct) {
            throw new SchemaDefinitionException(sprintf('A percentage of O is not allowed for the "%s" relationship', $this->getType()));
        }

        $this->percentage = $pct;
    }
}
