<?php

namespace Neoxygen\Neogen\Schema;

class RelationshipProperty extends Property
{
    protected $unique;

    public function isUnique()
    {
        if ($this->unique) {

            return true;
        }

        return false;
    }

    public function setUnique()
    {
        $this->unique = true;
    }
}
