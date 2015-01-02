<?php

namespace Neoxygen\Neogen\Processor;

use Neoxygen\Neogen\Exception\SchemaDefinitionException as SchemaException,
    Neoxygen\Neogen\Schema\GraphSchema;

class VertEdgeProcessor
{
    private $identifiers = [];

    private $nodes = [];

    private $edges = [];

    private $nodesByIdentifier = [];

    private $nodeDefinitions = [];

    /**
     *
     *
     * @param array $schema
     */
    public function process(GraphSchema $schema)
    {
        $schemaNodes = $schema->getNodes(); // Required for PHP 5.4 support
        if (empty($schemaNodes)) {
            throw new SchemaException('You need to define at least one node to generate');
        }

        foreach ($schema->getNodes() as $node) {
            if (!in_array($node->getIdentifier(), $this->identifiers)) {
                $this->identifiers[] = $node->getIdentifier();
            }
            if (!array_key_exists($node->getIdentifier(), $this->nodeDefinitions)) {
                $this->nodeDefinitions[$node->getIdentifier()] = $node;
            }
            $count = $node->getAmount();
            $x = 1;
            while ($x <= $count) {
                $id = sha1(microtime(true) . rand(0, 100000000000));
                $inode = [];
                $inode['neogen_id'] = $id;
                $inode['labels'] = $node->getLabels()->toArray();
                $inode['identifier'] = $node->getIdentifier();
                $inode['properties'] = $node->getProperties();
                $this->nodes[] = $inode;
                $this->nodesByIdentifier[$node->getIdentifier()][] = $id;
                $x++;
            }
        }

        $rx = 0;
        foreach ($schema->getRelationships() as $k => $rel) {
            $start = $rel->getStartNode();
            $end = $rel->getEndNode();
            $type = $rel->getType();
            $mode = $rel->getCardinality();
            $props = $rel->getProperties();

            if (!in_array($start, $this->identifiers) || !in_array($end, $this->identifiers)) {
                throw new SchemaException(sprintf('The start or end node of relationship "%s" is not defined', $rx));
            }

            // Currently only these modes supported
            $allowedModes = array('n..1', '1..n', 'n..n', '1..1');
            if (!in_array($mode, $allowedModes)) {
                throw new SchemaException(sprintf('The cardinality "%s" for the relationship is not supported', $mode));
            }

            switch ($mode) {
                case 'n..1':
                    foreach ($this->nodesByIdentifier[$start] as $node) {
                        $endNodes = $this->nodesByIdentifier[$end];
                        shuffle($endNodes);
                        $endNode = current($endNodes);
                        $this->setEdge($node, $endNode, $type, $props, $start, $end);
                    }
                    break;

                case '1..1':
                    $startNodes = $this->nodesByIdentifier[$start];
                    $endNodes = $this->nodesByIdentifier[$end];
                    $startCount = count($startNodes);
                    if ($start === $end) {
                        for ($i = 0; $i < $startCount; $i++) {
                            $x = array_shift($startNodes);
                            $y = $startNodes[0];
                            $this->setEdge($x, $y, $type, $props, $start, $end);
                            $i++;
                        }
                        break;
                    }
                    for ($i = 0; $i <= $startCount -1; $i++) {
                        if (!empty($endNodes)) {
                            $endN = array_shift($endNodes);
                            $startN = array_shift($startNodes);
                            $this->setEdge($startN, $endN, $type, $props, $start, $end);
                        }
                    }
                    break;

                case 'n..n':
                    $endNodes = $this->nodesByIdentifier[$end];
                    $max = count($endNodes);
                    $pct = $max <= 100 ? 0.8 : 0.55;
                    $maxi = round($max * $pct);
                    $random = rand(1, $maxi);
                    foreach ($this->nodesByIdentifier[$start] as $node) {
                        for ($i = 1; $i <= $random; $i++) {
                            reset($endNodes);
                            shuffle($endNodes);
                            $endNode = current($endNodes);
                            next($endNodes);
                            if ($endNode !== $node) {
                                $this->setEdge($node, $endNode, $type, $props, $start, $end);
                            }

                        }
                    }
                    break;
                case '1..n':
                    $startNodes = $this->nodesByIdentifier[$start];
                    $endNodes = $this->nodesByIdentifier[$end];
                    foreach ($endNodes as $endNode) {
                        $startNode = $startNodes[array_rand($startNodes)];
                        $this->setEdge($startNode, $endNode, $type, $props, $start, $end);
                    }
                    break;
            }
            $rx++;
        }

        return $this;

    }

    public function setEdge($startId, $endId, $type, $properties = [], $startIdentifier, $endIdentifier)
    {
        $this->edges[] = [
            'source' => $startId,
            'target' => $endId,
            'type' => $type,
            'properties' => $properties,
            'source_label' => $this->nodeDefinitions[$startIdentifier]->getLabels()->first(),
            'target_label' => $this->nodeDefinitions[$endIdentifier]->getLabels()->first()
        ];
    }

    public function getEdges()
    {
        return $this->edges;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getNodesByIdentifier()
    {
        return $this->nodesByIdentifier;
    }

    public function getGraph()
    {
        $g = [
            'nodes' => $this->nodes,
            'edges' => $this->edges
        ];

        return $g;
    }
}
