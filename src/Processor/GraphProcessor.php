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

    public function processNodeDefinition(NodeDefinition $node, $seed)
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

    public function processRelationshipDefinition(RelationshipDefinition $relationship, $seed)
    {
        switch ($relationship->getCardinality()) {
            case 'n..1':
                return $this->processNTo1Relationship($relationship, $seed);
            case '1..1':
                return $this->process1To1Relationship($relationship, $seed);
            case 'n..n':
                return $this->processNToNRelationship($relationship, $seed);
        }
    }

    public function processNTo1Relationship(RelationshipDefinition $relationship, $seed)
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

    public function process1To1Relationship(RelationshipDefinition $relationship, $seed)
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

    public function processNToNRelationship(RelationshipDefinition $relationship, $seed)
    {
        $collection = new ObjectCollection();
        $targetCount = $this->getTargetNodesCount($relationship, $this->nodesByIdentifier[$relationship->getEndNode()]);
        $startNodes = $this->nodesByIdentifier[$relationship->getStartNode()];
        $endNodes = $this->nodesByIdentifier[$relationship->getEndNode()];
        foreach ($startNodes as $i => $startNode) {
            print($i);
            $usedTargets = [];
            $x = 0;
            while ($x < $targetCount) {
                $endNodePosition = $this->getNotUsedNodePosition($usedTargets, $endNodes, $startNode);
                $endNode = $endNodes->get($endNodePosition);
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
                $usedTargets[] = $i;
                $x++;
            }
        }

        return $collection;
    }

    /**
     * @param RelationshipDefinition $relationship
     * @param ObjectCollection $targetNodes
     * @return int
     */
    public function getTargetNodesCount(RelationshipDefinition $relationship, ObjectCollection $targetNodes)
    {
        $targetCount = $targetNodes->count();
        if ($relationship->hasPercentage()) {
            $pct = $relationship->getPercentage();
        } else {
            $pct = $targetCount <= 100 ? 60 : 20;
        }

        $percentage = $pct/100;
        $count = round($targetCount*$percentage);

        return (int) $count;
    }

    public function createRelationship($type, $sourceId, $sourceLabel, $targetId, $targetLabel, $properties, $seed)
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

    private function getNotUsedNodePosition($usedNodes, ObjectCollection $collection, $avoidSelf = null, $shuffle = false)
    {
        foreach ($collection as $k => $n) {
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