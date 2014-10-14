<?php

namespace Neoxygen\Neogen\Converter;

use Neoxygen\Neogen\Graph\Graph;
use Faker\Factory;

class GraphJSONConverter implements ConverterInterface
{
    private $labels;

    private $nodes;

    private $edges;

    private $faker;

    private $style;

    public function __construct()
    {
        $this->nodes = [];
        $this->edges = [];
        $this->labels = [];
        $this->style = [];
        $this->clusterColors = [];
        $this->faker = Factory::create();
    }

    public function convert(Graph $graph)
    {
        foreach ($graph->getNodes() as $node){
            if (!in_array($node['label'], $this->labels)) {
                $this->labels[] = $node['label'];
                $this->setClusterForLabel($node['label']);
            }
            $n = [];
            $n['_id'] = $node['neogen_id'];
            $n['label'] = $node['label'];
            $n['properties'] = $node['properties'];
            $n['cluster'] = $this->clusterColors[$node['label']];
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
        foreach ($this->labels as $path) {
            $style = [];
            $k = 'nodeStyle.label.'.$path;
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
