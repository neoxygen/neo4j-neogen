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

    public function __construct()
    {
        $this->vertEdgeProcessor = new VertEdgeProcessor();
        $this->propertyProcessor = new PropertyProcessor();
        $this->modelLayersHandler = new ModelLayerHandler();
    }

    public function generate(GraphSchemaDefinition $schema)
    {
        print_r($schema);
        $graph = new Graph();

        $this->modelLayersHandler->findModelResources();
        foreach ($schema->getNodes() as $identifier => $node){
            foreach($node['labels'] as $label){
                if (array_key_exists($label, $this->modelLayersHandler->getModels())){
                    $oldProps = null !== $node['properties'] ? $node['properties'] : array();
                    $newProps = $this->mergeModelProperties($oldProps, $label);
                    $node['properties'] = $newProps;
                    $schema->replaceNode($identifier, $node);
                }
            }
        }
        print_r($schema);

        $vertEdge = $this->vertEdgeProcessor->process($schema);
        $this->propertyProcessor->process($vertEdge, $graph);

        return $graph;
    }

    public function mergeModelProperties(array $properties, $label)
    {
        foreach ($this->modelLayersHandler->getModels()[$label]['properties'] as $property => $values){
            if (!isset($properties[$property])){
                $properties[$property] = $values;
            }
        }
        return $properties;
    }
}