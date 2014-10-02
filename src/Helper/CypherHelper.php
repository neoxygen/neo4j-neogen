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

    public function addRelationship($start, $end, $type, array $properties = array())
    {
        $sa = 'r'.sha1($start.microtime());
        $es = 'r'.sha1($end.microtime());
        $q = 'MERGE ('.$sa.' { neogen_id: "'.$start.'" }) ';
        $q .= 'MERGE ('.$es.' { neogen_id: "'.$end.'" }) ';

        if (!empty($properties)) {
            $props = ' { ';
            $i = 0;
            $max = count($properties);
            foreach ($properties as $key => $value) {
                if (is_string($value)) {
                    $val = '"'.$value.'"';
                } elseif (is_int($value)) {
                    $val = 'toInt('.$value.')';
                } elseif (is_float($value)) {
                    $val = 'toFloat('.$value.')';
                } else {
                    $val = $value;
                }
                $props .= $key.':'.$val;
                if ($i < $max-1) {
                    $props .= ',';
                }

                $i++;
            }

            $props .= '}';
        } else {
            $props = '';
        }

        $q .= 'MERGE ('.$sa.')-[:'.$type.$props.']->('.$es.') ';
        if (!empty($props)) {
            //print_r($q);
            //exit();
        }
        return $q;
    }
}