<?php

namespace Neoxygen\Neogen\Converter;

use Neoxygen\Neogen\Graph\Graph;

class CypherStatementsConverter implements ConverterInterface
{
    private $constraintsStatements = [];

    private $nodeStatements = [];

    private $edgeStatements = [];

    public function convert(Graph $graph)
    {
        $labels = [];
        $nodesByIdentifier = [];
        $identifierToLabelMap = [];
        $edgesByType = [];
        $edgeTypes = [];

        foreach ($graph->getNodes() as $node) {
            $nodesByIdentifier[$node['identifier']][] = $node;
            if (!array_key_exists($node['identifier'], $identifierToLabelMap)) {
                $identifierToLabelMap[$node['identifier']] = $node['labels'][0];
                $labels[] = $node['labels'][0];
            }
        }

        // Creating constraints statements
        foreach ($nodesByIdentifier as $nodeIdentifier => $node) {
            $identifier = strtolower($nodeIdentifier);
            $label = $identifierToLabelMap[$nodeIdentifier];

            $ccs = 'CREATE CONSTRAINT ON (' . $identifier . ':' . $label . ') ASSERT ' . $identifier . '.neogen_id IS UNIQUE';
            $cst = ['statement' => $ccs];
            $this->constraintsStatements[] = $cst;
        }

        // Creating node creation statements
        foreach ($nodesByIdentifier as $key => $type) {
            if (!isset($type[0])) {
                continue;
            }
            $node = $type[0];
            $label = $node['labels'][0];
            $labelsCount = count($node['labels']);
            $idx = strtolower($key);
            $q = 'UNWIND {props} as prop'.PHP_EOL;
            $q .= 'MERGE ('.$idx.':'.$label.' {neogen_id: prop.neogen_id})'.PHP_EOL;
            if ($labelsCount > 1) {
                $q .= 'SET ';
                $li = 1;
                foreach ($node['labels'] as $lbl) {
                    if ($lbl !== $label) {
                        $q .= $idx.' :'.$lbl;
                        if ($li < $labelsCount) {
                            $q .= ', ';
                        }
                    }
                    $li++;
                }
            }
            $propsCount = count($node['properties']);
            if ($propsCount > 0) {
                if ($labelsCount > 1) {
                    $q .= ', ';
                } else {
                    $q .= 'SET ';
                }
                $i = 1;
                foreach ($node['properties'] as $property => $value) {
                        $q .= $idx.'.'.$property.' = prop.properties.'.$property;
                        if ($i < $propsCount) {
                            $q .= ', ';
                        }
                        $i++;
                }
            }

            $nts = [
                'statement' => $q,
                'parameters' => [
                    'props' => $nodesByIdentifier[$key]
                ]
            ];
            $this->nodeStatements[] = $nts;
        }

        foreach ($graph->getEdges() as $edge) {
            $edgeType = $edge['source_label'] . $edge['type'] . $edge['target_label'];
            if (!in_array($edgeType, $edgeTypes)) {
                $edgeTypes[] = $edgeType;
            }
            $edgesByType[$edgeType][] = $edge;
        }

        // Creating edge statements
        $i = 1;
        foreach ($edgesByType as $type => $rels) {
            if (!isset($type[0])) {
                continue;
            }
            $rel = $rels[0];
            $q = 'UNWIND {pairs} as pair'.PHP_EOL;
            $q .= 'MATCH (start:'.$rel['source_label'].' {neogen_id: pair.source}), (end:'.$rel['target_label'].' {neogen_id: pair.target})'.PHP_EOL;
            $q .= 'MERGE (start)-[edge:'.$rel['type'].']->(end)'.PHP_EOL;
            $propsCount = count($rel['properties']);
            if ($propsCount > 0) {
                $q .= 'SET ';
                $i = 1;
                foreach ($rel['properties'] as $property => $value) {
                    $q .= 'edge.'.$property.' = pair.properties.'.$property;
                    if ($i < $propsCount) {
                        $q .= ', '.PHP_EOL;
                    }
                    $i++;
                }
            }
            $ets = [
                'statement' => $q,
                'parameters' => [
                    'pairs' => $edgesByType[$type]
                ]
            ];
            $this->edgeStatements[] = $ets;
            $i++;
        }

        $this->addRemoveIdsStatements($labels);

        return $this->getStatements();

    }

    public function getStatements()
    {
        $statements = array(
            'constraints' => $this->constraintsStatements,
            'nodes' => $this->nodeStatements,
            'edges' => $this->edgeStatements
        );

        return $statements;
    }

    public function getEdgeStatements()
    {
        return $this->edgeStatements;
    }

    public function getConstraintStatements()
    {
        return $this->constraintsStatements;
    }

    public function getNodeStatements()
    {
        return $this->nodeStatements;
    }

    private function addRemoveIdsStatements(array $labels)
    {
        $i = 1;
        foreach ($labels as $label) {
            $q = 'MATCH (n'.$i.':'.$label.') REMOVE n'.$i.'.neogen_id;';
            $statement = [
                'statement' => $q
            ];

            $this->edgeStatements[] = $statement;
            $i++;
        }
    }
}
