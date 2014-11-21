<?php

namespace Neoxygen\Neogen\Converter;

use Neoxygen\Neogen\Graph\Graph;

class StandardCypherConverter implements ConverterInterface
{
    private $statements = [];

    public function convert(Graph $graph)
    {
        $labels = [];

        $identifierToLabelMap = [];

        $nodesByIdentifier = [];

        foreach ($graph->getNodes() as $node) {
            $nodesByIdentifier[$node['identifier']][] = $node;
            if (!array_key_exists($node['identifier'], $identifierToLabelMap)) {
                $identifierToLabelMap[$node['identifier']] = $node['labels'][0];
                $labels[] = $node['labels'][0];
            }
        }

        foreach ($nodesByIdentifier as $nodeIdentifier => $node) {
            $identifier = strtolower($nodeIdentifier);
            $label = $identifierToLabelMap[$nodeIdentifier];

            $ccs = 'CREATE CONSTRAINT ON (' . $identifier . ':' . $label . ') ASSERT ' . $identifier . '.neogen_id IS UNIQUE;';
            $this->statements[] = $ccs;
        }
        $i = 1;
        foreach ($graph->getNodes() as $node) {
            $label = $identifierToLabelMap[$node['identifier']];
            $labelsCount = count($node['labels']);
            if (!isset($node['neogen_id']) && isset($node['_id'])) {
                $node['neogen_id'] = $node['_id'];
            }
            $identifier = 'n'.$i;
            $statement = 'MERGE ('.$identifier.':'.$label.' {neogen_id: \''.$node['neogen_id'].'\' })';
            if ($labelsCount > 1) {
                $statement .= 'SET ';
                $li = 1;
                foreach ($node['labels'] as $lbl) {
                    if ($lbl !== $label) {
                        $statement .= $identifier.' :'.$lbl;
                        if ($li < $labelsCount) {
                            $statement .= ', ';
                        }
                    }
                    $li++;
                }
            }
            $statement .= PHP_EOL;
            if (!empty($node['properties'])) {
                if ($labelsCount > 1) {
                    $statement .= ', ';
                } else {
                    $statement .= 'SET ';
                }
                $xi = 1;
                $propsCount = count($node['properties']);
                foreach ($node['properties'] as $prop => $value) {
                    if (is_string($value)) {
                        $val = '\''.addslashes($value).'\'';
                    } elseif (is_int($value)) {
                        $val = $value;
                    } elseif (is_float($value)) {
                        $val = $value;
                    } else {
                        $val = addslashes($value);
                    }
                    $statement .= $identifier.'.'.$prop.' = '.$val;
                    if ($xi < $propsCount) {
                        $statement .= ', ';
                    }
                    $xi++;
                }
            }
            $statement .= ';';
            $this->statements[] = $statement;
            $i++;
        }

        $e = 1;
        foreach ($graph->getEdges() as $rel) {
            $starti = 's'.$e;
            $endi = 'e'.$e;
            $eid = 'edge'.$e;
            $q = 'MATCH ('.$starti.':'.$rel['source_label'].' {neogen_id: \''.$rel['source'].'\'}), ('.$endi.':'.$rel['target_label'].' { neogen_id: \''.$rel['target'].'\'})'.PHP_EOL;
            $q .= 'MERGE ('.$starti.')-['.$eid.':'.$rel['type'].']->('.$endi.')'.PHP_EOL;
            if (empty($rel['properties'])) {
                $q .= ';';
            }
            if (!empty($rel['properties'])) {
                $q .= 'SET ';
                $xi = 1;
                $propsCount = count($rel['properties']);
                foreach ($rel['properties'] as $prop => $value) {
                    if (is_string($value)) {
                        $val = '\''.addslashes($value).'\'';
                    } elseif (is_int($value)) {
                        $val = $value;
                    } elseif (is_float($value)) {
                        $val = $value;
                    } else {
                        $val = addslashes($value);
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

        $ssi = 1;
        foreach ($labels as $label) {
            $this->statements[] = 'MATCH (n'.$ssi.':'.$label.') REMOVE n'.$ssi.'.neogen_id;';
            $ssi++;
        }

        return $this->statements;

    }

    public function getStatements()
    {
        return $this->statements;
    }
}
