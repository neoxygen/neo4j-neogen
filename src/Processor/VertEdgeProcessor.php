<?php

namespace Neoxygen\Neogen\Processor;

use Neoxygen\Neogen\Exception\SchemaException;

class VertEdgeProcessor
{
    private $labels = [];

    private $nodes = [];

    private $edges = [];

    private $nodesByTypes = [];

    /**
     * Generate the queries for the creation of the nodes and relationships based on a schema file
     * It also add constraints for all node labels on the "neogen_id" property
     *
     *
     * @param array $schema
     */
    public function process(array $schema)
    {
        if (!isset($schema['nodes'])) {
            throw new SchemaException('You need to define at least one node to generate');
        }

        foreach ($schema['nodes'] as $node) {
            if (!in_array($node['label'], $this->labels)) {
                $this->labels[] = $node['label'];
            }
            $count = isset($node['count']) ? $node['count'] : range(10, 50);
            $x = 1;
            while ($x <= $count) {
                $id = sha1(microtime(true) . rand(0, 100000000000));
                $inode = [];
                $inode['neogen_id'] = $id;
                $inode['label'] = $node['label'];
                $np = isset($node['properties']) ? $node['properties'] : [];
                $inode['properties'] = $np;
                $this->nodes[] = $inode;
                $this->nodesByTypes[$node['label']][] = $id;
                $x++;

            }
        }

        foreach ($schema['relationships'] as $k => $rel) {
            $start = $rel['start'];
            $end = $rel['end'];
            $type = $rel['type'];
            $mode = $rel['mode'];
            $props = isset($rel['properties']) ? $rel['properties'] : null;

            if (!in_array($start, $this->labels) || !in_array($end, $this->labels)) {
                throw new SchemaException(sprintf('The start or end node of relationship "%s" is not defined', $k));
            }

            // Currently only these modes supported
            $allowedModes = array('n..1', '1..n', 'n..n');
            if (!in_array($mode, $allowedModes)) {
                throw new SchemaException(sprintf('The cardinality "%s" for the relationship is not supported', $mode));
            }

            switch ($mode) {
                case 'n..1':
                    foreach ($this->nodesByTypes[$start] as $node) {
                        $endNodes = $this->nodesByTypes[$end];
                        shuffle($endNodes);
                        $endNode = current($endNodes);
                        $this->setEdge($node, $endNode, $type, $props, $start, $end);
                    }
                    break;

                case 'n..n':
                    $endNodes = $this->nodesByTypes[$end];
                    $max = count($endNodes);
                    $pct = $max <= 100 ? 0.8 : 0.55;
                    $maxi = round($max * $pct);
                    $random = rand(1, $maxi);
                    foreach ($this->nodesByTypes[$start] as $node) {
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
                    $cstart = count($this->nodesByTypes[$start]);
                    $cend = count($this->nodesByTypes[$end]);
                    if ($cstart <= $cend){
                        $left = $cend - $cstart;
                        $free = 1;
                        if ($left > 1){
                            $round = round($left / $cstart, null, PHP_ROUND_HALF_UP);
                            $free = $round >= 1 ? $round : 1;
                        }
                        $endNodes = $this->nodesByTypes[$end];
                        $x = 1;
                        foreach($this->nodesByTypes[$start] as $startNode){
                            for($i=1; $i <= $free; $i++){
                                $endNode = array_shift($endNodes);
                                $this->setEdge($startNode, $endNode, $type, $props, $start, $end);
                            }
                            if ($x === $cstart){
                                $remaining = count($endNodes);
                                for ($i = 1; $i <= $remaining; $i++){
                                    $endNode = array_shift($endNodes);
                                    $this->setEdge($startNode, $endNode, $type, $props, $start, $end);
                                }
                            }
                            $x++;
                        }
                    } else {
                        $approx = round($cstart / $cend);
                        $endNodes = $this->nodesByTypes[$end];
                        foreach ($this->nodesByTypes[$start] as $startNode){
                            $to = (count($endNodes) >= $approx) ? $approx : count($endNodes);
                            for ($i = 1; $i <= $to; $i++){
                                $endNode = array_shift($endNodes);
                                $this->setEdge($startNode, $endNode, $type, $props, $start, $end);
                            }
                        }
                    }
                    break;
            }
        }

        return $this;

    }

    public function setEdge($startId, $endId, $type, $properties = [], $startlabel, $endlabel)
    {
        $this->edges[] = [
            'source' => $startId,
            'target' => $endId,
            'type' => $type,
            'properties' => $properties,
            'source_label' => $startlabel,
            'target_label' => $endlabel
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

    public function getNodesByType()
    {
        return $this->nodesByTypes;
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