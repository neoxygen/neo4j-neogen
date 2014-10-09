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

            $ccs = 'CREATE CONSTRAINT ON (' . $identifier . ':' . $label . ') ASSERT ' . $identifier . '.neogen_id IS UNIQUE';
            $this->statements[] = $ccs;
        }
        $i = 1;
        foreach ($graph->getNodes() as $node) {
            $identifier = 'n'.$i;
            $statement = 'MERGE ('.$identifier.':'.$node['label'].' {neogen_id: '.$node['neogen_id'].' })'.PHP_EOL;
            if (!empty($node['properties'])) {
                $statement .= 'SET ';
                $xi = 1;
                $propsCount = count($node['properties']);
                foreach ($node['properties'] as $prop => $value) {
                    $statement .= $identifier.'.'.$prop.' = '.$value;
                    if ($xi < $propsCount) {
                        $statement .= ', ';
                    }
                    $statement .= PHP_EOL;
                    $xi++;
                }
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
            $q = 'MATCH ('.$starti.' {neogen_id: \''.$rel['source'].'\'}), ('.$endi.' { neogen_id: \''.$rel['target'].'\'})'.PHP_EOL;
            $q .= 'MERGE ('.$starti.')-['.$eid.':'.$rel['type'].']->('.$endi.')'.PHP_EOL;
            if (!empty($rel['properties'])) {
                $q .= 'SET ';
                $xi = 1;
                $propsCount = count($rel['properties']);
                foreach ($rel['properties'] as $prop => $value) {
                    $q .= $eid.'.'.$prop.' = '.$value;
                    if ($xi < $propsCount) {
                        $q .= ', ';
                    }
                    $eid .= PHP_EOL;
                    $xi++;
                }
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