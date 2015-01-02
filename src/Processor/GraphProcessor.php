<?php

namespace Neoxygen\Neogen\Processor;

use Neoxygen\Neogen\FakerProvider\Faker;
use Neoxygen\Neogen\Schema\GraphSchema;
use Neoxygen\Neogen\Graph\Node;
use Neoxygen\Neogen\Schema\Node as NodeDefinition;
use Neoxygen\Neogen\Schema\Property;
use Neoxygen\Neogen\Schema\Relationship as RelationshipDefinition;
use Neoxygen\Neogen\Graph\Relationship;
use Neoxygen\Neogen\Util\ObjectCollection;

class GraphProcessor
{
    protected $faker;

    protected $nodesByIdentifier;

    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    public function process(GraphSchema $schema, $seed = null)
    {
        $this->nodesByIdentifier = [];
        $graphNodes = new ObjectCollection();
        $graphRels = new ObjectCollection();

        foreach ($schema->getNodes() as $node) {
            $nodes = $this->processNodeDefinition($node, $seed);
            $graphNodes->add($nodes->toArray());
            $this->nodesByIdentifier[$node->getIdentifier()] = $nodes;
        }

        foreach ($schema->getRelationships() as $relationship) {
            $relationships = $this->processRelationshipDefinition($relationship, $seed);
            $graphRels->add($relationships->toArray());
        }

        print_r($graphRels);
    }

    private function processNodeDefinition(NodeDefinition $node, $seed)
    {
        $collection = new ObjectCollection();
        for ($i=0; $i < $node->getAmount(); $i++) {
            $id = $this->faker->generate('uuid', [], null, true);
            $n = new Node($id);
            $n->addLabels($node->getLabels()->toArray());
            foreach ($node->getProperties() as $property) {
                $n->addProperty($property->getName(), $this->getFakeData($property, $seed));
            }
            $collection->add($n);
        }

        return $collection;
    }

    private function processRelationshipDefinition(RelationshipDefinition $relationship, $seed)
    {
        switch ($relationship->getCardinality()) {
            case 'n..1':
                return $this->processNTo1Relationship($relationship, $seed);
            case '1..1':
                return $this->process1To1Relationship($relationship, $seed);
        }
    }

    private function processNTo1Relationship(RelationshipDefinition $relationship, $seed)
    {
        $collection = new ObjectCollection();
        foreach ($this->nodesByIdentifier[$relationship->getStartNode()] as $startNode) {
            $endNode = $this->getRandomNode($this->nodesByIdentifier[$relationship->getEndNode()]);
            $r = $this->createRelationship($relationship->getType(),
                $startNode->getId(),
                $startNode->getLabel(),
                $endNode->getId(),
                $endNode->getLabel(),
                $relationship->getProperties(),
                $seed);
            $collection->add($r);
        }

        return $collection;

    }

    private function process1To1Relationship(RelationshipDefinition $relationship, $seed)
    {
        $collection = new ObjectCollection();
        $usedEnds = [];
        $startNodes = $this->nodesByIdentifier[$relationship->getStartNode()];
        $endNodes = $this->nodesByIdentifier[$relationship->getEndNode()];
        $maxEnds = $endNodes->count();
        $i = 0;
        foreach ($startNodes as $startNode) {
            if (!in_array($i, $usedEnds)) {
                $endPosition = $this->getNotUsedNodePosition($usedEnds, $endNodes, $startNode);
                if (null !== $endPosition) {
                    $endNode = $endNodes->get($endPosition);
                    $rel = $this->createRelationship(
                        $relationship->getType(),
                        $startNode->getId(),
                        $startNode->getLabel(),
                        $endNode->getId(),
                        $endNode->getLabel(),
                        $relationship->getProperties(),
                        $seed
                    );
                    $collection->add($rel);
                    $usedEnds[] = $endPosition;
                    $usedEnds[] = $i;
                }
            }
            $i++;
        }

        return $collection;
    }

    private function createRelationship($type, $sourceId, $sourceLabel, $targetId, $targetLabel, $properties, $seed)
    {
        $relationship = new Relationship();
        $relationship->setType($type);
        $relationship->setSourceId($sourceId);
        $relationship->setSourceLabel($sourceLabel);
        $relationship->setTargetId($targetId);
        $relationship->setTargetLabel($targetLabel);
        foreach ($properties as $property) {
            $relationship->addProperty($property->getName(), $this->getFakeData($property, $seed));
        }

        return $relationship;
    }

    private function getRandomNode(ObjectCollection $nodes)
    {
        $max = $nodes->count();
        $i = rand(0, $max-1);

        return $nodes->get($i);
    }

    private function getNotUsedNodePosition($usedNodes, ObjectCollection $collection, $avoidSelf = null)
    {
        $coll = $collection->toArray();
        foreach ($coll as $k => $n) {
            if (!in_array($k, $usedNodes)) {
                if (null !== $avoidSelf) {
                    if ($n !== $avoidSelf) {
                        return $k;
                    }
                } else {
                    return $k;
                }
            }
        }

        return null;
    }

    private function getFakeData(Property $property, $seed)
    {
        $v = $this->faker->generate($property->getProvider(), $property->getArguments(), $seed, $property->isUnique());

        return $this->sanitizeValueForGraph($v);
    }

    private function sanitizeValueForGraph($v)
    {
        if ($v instanceof \DateTime) {
            return $v->format('Y-m-d H:i:s');
        }

        return $v;
    }

}