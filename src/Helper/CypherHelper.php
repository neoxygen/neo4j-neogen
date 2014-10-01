<?php

namespace Neoxygen\Neogen\Helper;

class CypherHelper
{

    public function openMerge()
    {
        return 'MERGE (';
    }

    public function addNodeLabel($alias = null, $label)
    {
        if (null === $alias) {
            $alias = str_replace('.','','n'.microtime(true).rand(0,100000000000));
        }

        return $alias.':'.$label.' ';
    }

    public function closeMerge()
    {
        return ') ';
    }

    public function openNodePropertiesBracket()
    {
        return '{ ';
    }

    public function closeNodePropertiesBracket()
    {
        return '}';
    }

    public function addNodeProperty($key, $value)
    {
        if (is_string($value)) {
            $value = '"'.$value.'"';
        } elseif (is_int($value)) {
            $value = 'toInt('.$value.')';
        }
        return $key.':'.$value;
    }

    public function addRelationship($start, $end, $type)
    {
        $sa = 'r'.sha1($start.microtime());
        $es = 'r'.sha1($end.microtime());
        $q = 'MERGE ('.$sa.' { neogen_id: "'.$start.'" }) ';
        $q .= 'MERGE ('.$es.' { neogen_id: "'.$end.'" }) ';
        $q .= 'MERGE ('.$sa.')-[:'.$type.']->('.$es.') ';

        return $q;
    }
}