<?php

namespace Neoxygen\Neogen\Converter;

use Neoxygen\Neogen\Graph\Graph;
use Faker\Factory;

class GraphJSONConverter implements ConverterInterface
{
    private $identifiers;

    private $nodes;

    private $edges;

    private $faker;

    private $style;

    public function __construct()
    {
        $this->nodes = [];
        $this->edges = [];
        $this->identifiers = [];
        $this->style = [];
        $this->clusterColors = [];
        $this->faker = Factory::create();
    }

    public function convert(Graph $graph)
    {
        foreach ($graph->getNodes() as $node){
            if (!in_array($node['identifier'], $this->identifiers)) {
                $this->identifiers[] = $node['identifier'];
                $this->setClusterForLabel($node['identifier']);
            }
            $n = [];
            $n['_id'] = $node['neogen_id'];
            $n['identifier'] = $node['identifier'];
            $n['properties'] = $node['properties'];
            $n['labels'] = $node['labels'];
            $n['cluster'] = $this->clusterColors[$node['identifier']];
            $this->nodes[] = $n;
        }

        foreach ($graph->getEdges() as $edge) {
            $e = [];
            $e['_source'] = $edge['source'];
            $e['_target'] = $edge['target'];
            $e['type'] = $edge['type'];
            $e['properties'] = $edge['properties'];
            $e['source_label'] = $edge['source_label'];
            $e['target_label'] = $edge['target_label'];
            $this->edges[] = $e;
        }

        $this->buildStyle();

        return $this->toJSON();
    }

    public function buildStyle()
    {
        foreach ($this->identifiers as $path) {
            $style = [];
            $k = 'nodeStyle.identifier.'.$path;
            $color = $this->faker->hexcolor;
            $style[] = ['fill' => $color];
            $this->style[$k] = $style;
        }
    }

    private function toJSON()
    {
        $g = [
            'style' => $this->style,
            'nodes' => $this->nodes,
            'edges' => $this->edges
        ];

        $json = json_encode($g);

        return $json;
    }

    private function setClusterForLabel($label)
    {
        $cluster = $this->faker->numberBetween(0, 12);
        if (in_array($cluster, $this->clusterColors) && count($this->clusterColors) < 12){
            $this->setClusterForLabel($label);
        }
        $this->clusterColors[$label] = $cluster;
    }
}
