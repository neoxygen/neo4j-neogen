<?php

namespace Neoxygen\Neogen\Schema;

class NodeProperty extends Property
{
    protected $indexed;

    protected $unique;

    public function isIndexed()
    {
        if ($this->indexed) {
            return true;
        }

        return false;
    }

    public function setIndexed()
    {
        $this->unique = false;
        $this->indexed = true;
    }

    public function isUnique()
    {
        if ($this->unique) {
            return true;
        }

        return false;
    }

    public function setUnique()
    {
        $this->indexed = false;
        $this->unique = true;
    }
}
