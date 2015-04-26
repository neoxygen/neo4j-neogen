<?php

namespace Neoxygen\Neogen\Graph;

class Relationship
{
    /**
     * @var
     */
    protected $type;

    /**
     * @var
     */
    protected $sourceId;

    /**
     * @var
     */
    protected $targetId;

    /**
     * @var
     */
    protected $sourceLabel;

    /**
     * @var
     */
    protected $targetLabel;

    protected $properties = [];

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = (string) $type;
    }

    /**
     * @return mixed
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * @return mixed
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * @param $v
     */
    public function setSourceId($v)
    {
        $this->sourceId = (string) $v;
    }

    /**
     * @param $v
     */
    public function setTargetId($v)
    {
        $this->targetId = (string) $v;
    }

    /**
     * @return mixed
     */
    public function getSourceLabel()
    {
        return $this->sourceLabel;
    }

    /**
     * @return mixed
     */
    public function getTargetLabel()
    {
        return $this->targetLabel;
    }

    /**
     * @param $v
     */
    public function setSourceLabel($v)
    {
        $this->sourceLabel = (string) $v;
    }

    /**
     * @param $v
     */
    public function setTargetLabel($v)
    {
        $this->targetLabel = (string) $v;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function addProperty($name, $v)
    {
        $this->properties[$name] = $v;
    }

    public function hasProperties()
    {
        if (!empty($this->properties)) {
            return true;
        }

        return false;
    }

    public function getProperty($key)
    {
        if (array_key_exists($key, $this->properties)) {
            return $this->properties[$key];
        }

        return null;
    }
}
