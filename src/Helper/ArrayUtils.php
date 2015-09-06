<?php

namespace GraphAware\Neogen\Helper;

class ArrayUtils
{
    public static function cleanEmptyStrings(array $array)
    {
        foreach ($array as $k => $v) {
            if ("" === $v) {
                unset($array[$k]);
            }
        }

        return $array;
    }
}