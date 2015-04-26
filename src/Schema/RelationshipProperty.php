<?php

namespace Neoxygen\Neogen\Schema;

class RelationshipProperty extends Property
{
    /**
     * @var null|bool
     */
    protected $unique;

    /**
     * Checks whether or not the relationship property should be unique
     *
     * @return bool
     */
    public function isUnique()
    {
        if ($this->unique) {
            return true;
        }

        return false;
    }

    /**
     * Sets the relationship property as unique
     */
    public function setUnique()
    {
        $this->unique = true;
    }
}
