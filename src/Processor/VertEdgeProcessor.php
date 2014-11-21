<?php

namespace Neoxygen\Neogen\Processor;

use Neoxygen\Neogen\Exception\SchemaException,
    Neoxygen\Neogen\Schema\GraphSchemaDefinition;

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
    public function process(GraphSchemaDefinition $schema)
    {
        $schemaNodes = $schema->getNodes(); // Required for PHP 5.4 support
        if (empty($schemaNodes)) {
            throw new SchemaException('You need to define at least one node to generate');
        }

        foreach ($schema->getNodes() as $identifier => $node) {
            if (!in_array($identifier, $this->identifiers)) {
                $this->identifiers[] = $identifier;
            }
            if (!array_key_exists($node['identifier'], $this->nodeDefinitions)) {
                $this->nodeDefinitions[$node['identifier']] = $node;
            }
            $count = isset($node['count']) ? $node['count'] : range(10, 50);
            $x = 1;
            while ($x <= $count) {
                $id = sha1(microtime(true) . rand(0, 100000000000));
                $inode = [];
                $inode['neogen_id'] = $id;
                $inode['labels'] = $node['labels'];
                $inode['identifier'] = $identifier;
                $np = isset($node['properties']) ? $node['properties'] : [];
                $inode['properties'] = $np;
                $this->nodes[] = $inode;
                $this->nodesByIdentifier[$identifier][] = $id;
                $x++;
            }
        }

        foreach ($schema->getEdges() as $k => $rel) {
            $start = $rel['start'];
            $end = $rel['end'];
            $type = $rel['type'];
            $mode = $rel['mode'];
            $props = isset($rel['properties']) ? $rel['properties'] : null;

            if (!in_array($start, $this->identifiers) || !in_array($end, $this->identifiers)) {
                throw new SchemaException(sprintf('The start or end node of relationship "%s" is not defined', $k));
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
            'source_label' => $this->nodeDefinitions[$startIdentifier]['labels'][0],
            'target_label' => $this->nodeDefinitions[$endIdentifier]['labels'][0]
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
