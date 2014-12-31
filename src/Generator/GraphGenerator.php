<?php

namespace Neoxygen\Neogen\Generator;

use Neoxygen\Neogen\Processor\VertEdgeProcessor,
    Neoxygen\Neogen\Processor\PropertyProcessor,
    Neoxygen\Neogen\Schema\GraphSchemaDefinition,
    Neoxygen\Neogen\Graph\Graph,
    Neoxygen\Neogen\ModelLayer\ModelLayerHandler;

class GraphGenerator
{
    private $vertEdgeProcessor;

    private $propertyProcessor;

    private $modelLayersHandler;

    public function __construct($seed = null)
    {
        $this->vertEdgeProcessor = new VertEdgeProcessor();
        $this->propertyProcessor = new PropertyProcessor($seed);
        $this->modelLayersHandler = new ModelLayerHandler();
    }

    public function generate(GraphSchemaDefinition $schema, $precalculationOnly = false)
    {
        $graph = new Graph();
        if ($precalculationOnly) {
            $nCount = 0;
            foreach ($schema->getNodes() as $n) {
                $nCount = $nCount + $n['count'];
            }
            if ($nCount > 10000) {
                return array(
                    'nodes' => $nCount
                );
            }
        }

        $this->modelLayersHandler->findModelResources();
        foreach ($schema->getNodes() as $identifier => $node) {
            foreach ($node['models'] as $label) {
                if (array_key_exists($label, $this->modelLayersHandler->getModels())) {
                    $oldProps = null !== $node['properties'] ? $node['properties'] : array();
                    $newProps = $this->mergeModelProperties($oldProps, $label);
                    $node['properties'] = $newProps;
                    $schema->replaceNode($identifier, $node);
                }
            }
        }

        $vertEdge = $this->vertEdgeProcessor->process($schema);
        if ($precalculationOnly) {
            return $vertEdge->getGraph();
        }

        $this->propertyProcessor->process($vertEdge, $graph);

        return $graph;
    }

    public function mergeModelProperties(array $properties, $label)
    {
        foreach ($this->modelLayersHandler->getModels()[$label]['properties'] as $property => $values) {
            if (!isset($properties[$property])) {
                $properties[$property] = $values;
            }
        }

        return $properties;
    }
}
