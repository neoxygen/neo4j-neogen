<?php

namespace Neoxygen\Neogen\Converter;

use Neoxygen\Neogen\Graph\Graph;

class StandardCypherConverter implements ConverterInterface
{
    private $statements = [];

    public function convert(Graph $graph)
    {
        $labels = [];

        foreach ($graph->getNodes() as $node) {
            $nodesByLabel[$node['label']][] = $node;
            if (!in_array($node['label'], $labels)) {
                $labels[] = $node['label'];
            }
        }

        foreach ($labels as $label) {
            $identifier = strtolower($label);

            $ccs = 'CREATE CONSTRAINT ON (' . $identifier . ':' . $label . ') ASSERT ' . $identifier . '.neogen_id IS UNIQUE;';
            $this->statements[] = $ccs;
        }
        $i = 1;
        foreach ($graph->getNodes() as $node) {
            $identifier = 'n'.$i;
            $statement = 'MERGE ('.$identifier.':'.$node['label'].' {neogen_id: \''.$node['neogen_id'].'\' });'.PHP_EOL;
            if (!empty($node['properties'])) {
                $statement .= 'SET ';
                $xi = 1;
                $propsCount = count($node['properties']);
                foreach ($node['properties'] as $prop => $value) {
                    if (is_int($value)){
                        $val = '\''.$value.'\'';
                    } elseif (is_int($value)){
                        $val = 'toInt('.$value.')';
                    } elseif (is_float($value)){
                        $val = 'toFloat('.$value.')';
                    } else {
                        $val = $value;
                    }
                    $statement .= $identifier.'.'.$prop.' = '.$val;
                    if ($xi < $propsCount) {
                        $statement .= ', ';
                    }
                    $xi++;
                }
                $q .= ';';
            }
            $this->statements[] = $statement;
            $i++;
        }

        $e = 1;
        foreach ($graph->getEdges() as $rel)
        {
            $starti = 's'.$e;
            $endi = 'e'.$i;
            $eid = 'edge'.$i;
            $q = 'MATCH ('.$starti.':'.$rel['source_label'].' {neogen_id: \''.$rel['source'].'\'}), ('.$endi.':'.$rel['target_label'].' { neogen_id: \''.$rel['target'].'\'})'.PHP_EOL;
            $q .= 'MERGE ('.$starti.')-['.$eid.':'.$rel['type'].']->('.$endi.');'.PHP_EOL;
            if (!empty($rel['properties'])) {
                $q .= 'SET ';
                $xi = 1;
                $propsCount = count($rel['properties']);
                foreach ($rel['properties'] as $prop => $value) {
                    if (is_int($value)){
                        $val = '\''.$value.'\'';
                    } elseif (is_int($value)){
                        $val = 'toInt('.$value.')';
                    } elseif (is_float($value)){
                        $val = 'toFloat('.$value.')';
                    } else {
                        $val = $value;
                    }
                    $q .= $eid.'.'.$prop.' = '.$val;
                    if ($xi < $propsCount) {
                        $q .= ', ';
                    }
                    $xi++;
                }
                $q .= ';';
            }
            $this->statements[] = $q;
            $e++;
        }
    }

    public function getStatements()
    {
        return $this->statements;
    }
}