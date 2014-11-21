<?php

namespace Neoxygen\Neogen\Helper;

class CypherHelper
{

    /**
     * Opens the MERGE statement
     *
     * @return string
     */
    public function openMerge()
    {
        return 'MERGE (';
    }

    /**
     * Add the node alias and the node label
     *
     * @param  null   $alias
     * @param $label
     * @return string
     */
    public function addNodeLabel($alias = null, $label)
    {
        if (null === $alias) {
            $alias = str_replace('.','','n'.microtime(true).rand(0,100000000000));
        }

        return $alias.':'.$label.' ';
    }

    /**
     * Closes the merge statement
     *
     * @return string
     */
    public function closeMerge()
    {
        return ') ';
    }

    /**
     * Opens the node properties bracket
     *
     * @return string
     */
    public function openNodePropertiesBracket()
    {
        return '{ ';
    }

    /**
     * Closes the node properties bracket
     *
     * @return string
     */
    public function closeNodePropertiesBracket()
    {
        return '}';
    }

    /**
     * Add a node property key => value, should be used
     * between the "openNodePropertiesBracket" and "closeNodePropertiesBracket" methods
     *
     * @param $key
     * @param $value
     * @return string
     */
    public function addNodeProperty($key, $value)
    {
        if (is_string($value)) {
            $value = '"'.htmlentities($value, ENT_QUOTES, 'UTF-8').'"';
        } elseif (is_int($value)) {
            $value = 'toInt('.$value.')';
        }

        return $key.':'.$value;
    }

    /**
     * Add a relationship path
     * First it try to merge nodes, id's are taken from the already node generated ids
     *
     * Trying to MERGE the nodes could add payload to the query, but as depending of the amount
     * of nodes and relationships creations, the queries may be splitted in multiple statements
     * to avoid dealing with too large bodies in http requests, that's why we first need to
     * get the start and end nodes
     *
     * @param $start
     * @param $end
     * @param $type
     * @param  array  $properties
     * @return string
     */
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
                    $val = '"'.htmlentities($value, ENT_QUOTES, 'UTF-8').'"';
                } elseif (is_int($value)) {
                    $val = 'toInt('.$value.')';
                } elseif (is_float($value)) {
                    $val = 'toFloat('.$value.')';
                } else {
                    $val = htmlentities($value);
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
