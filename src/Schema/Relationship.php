<?php

namespace Neoxygen\Neogen\Schema;

use Neoxygen\Neogen\Util\ObjectCollection,
    Neoxygen\Neogen\Schema\RelationshipProperty;

class Relationship
{
    protected $type;

    protected $properties;

    public function __construct($type)
    {
        $this->type = (string) $type;
        $this->properties = new ObjectCollection();
    }

    public function getType()
    {
        return $this->type;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function addProperty(RelationshipProperty $property)
    {
        foreach ($this->properties as $prop) {
            if ($prop->getName() === $property->getName()) {
                $this->properties->removeElement($prop);
            }
        }

        return $this->properties->add($property);
    }

    public function hasProperties()
    {
        if ($this->properties->isEmpty()) {
            return false;
        }

        return true;
    }

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
}
