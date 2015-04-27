<?php

namespace Neoxygen\Neogen\Processor;

use Neoxygen\Neogen\FakerProvider\Faker;
use Neoxygen\Neogen\Graph\Graph;
use Neoxygen\Neogen\Schema\GraphSchema;
use Neoxygen\Neogen\Graph\Node;
use Neoxygen\Neogen\Schema\Node as NodeDefinition;
use Neoxygen\Neogen\Schema\Property;
use Neoxygen\Neogen\Schema\Relationship as RelationshipDefinition;
use Neoxygen\Neogen\Graph\Relationship;
use Neoxygen\Neogen\Util\ObjectCollection;

class GraphProcessor
{
    /**
     * @var Faker
     */
    protected $faker;

    /**
     * @var ObjectCollection
     */
    protected $nodesByIdentifier;

    /**
     * @var ObjectCollection
     */
    protected $nodes;

    /**
     * @var ObjectCollection
     */
    protected $relationships;

    /**
     * @param Faker $faker
     */
    public function __construct(Faker $faker)
    {
        $this->faker = $faker;
    }

    /**
     * @param  GraphSchema $schema
     * @param  null|int    $seed
     * @return Graph
     */
    public function process(GraphSchema $schema, $seed = null)
    {
        $this->nodesByIdentifier = new ObjectCollection();
        $this->nodes = new ObjectCollection();
        $this->relationships = new ObjectCollection();

        foreach ($schema->getNodes() as $node) {
            $this->processNodeDefinition($node, $seed);
        }

        foreach ($schema->getRelationships() as $relationship) {
            $this->processRelationshipDefinition($relationship, $seed);
        }

        $graph = new Graph();
        $graph->setNodes($this->nodes);
        $graph->setEdges($this->relationships);
        $graph->setSchema($schema);

        return $graph;
    }

    /**
     * @param  NodeDefinition   $node
     * @param  null|int         $seed
     * @return ObjectCollection
     */
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
            $this->nodes->add($n);
        }

        $this->nodesByIdentifier->set($node->getIdentifier(), $collection);
    }

    /**
     * @param RelationshipDefinition $relationship
     * @param null|int               $seed
     */
    public function processRelationshipDefinition(RelationshipDefinition $relationship, $seed)
    {
        switch ($relationship->getCardinality()) {
            case 'n..1':
                $this->processNTo1Relationship($relationship, $seed);
                break;
            case '1..1':
                $this->process1To1Relationship($relationship, $seed);
                break;
            case 'n..n':
                $this->processNToNRelationship($relationship, $seed);
                break;
            case '1..n':
                $this->process1ToNRelationship($relationship, $seed);
                break;
            default:
                throw new \RuntimeException(sprintf('Unable to process relationship with "%s" cardinality', $relationship->getCardinality()));
        }
    }

    /**
     * @param RelationshipDefinition $relationship
     * @param null|int               $seed
     */
    public function processNTo1Relationship(RelationshipDefinition $relationship, $seed)
    {
        foreach ($this->nodesByIdentifier[$relationship->getStartNode()] as $startNode) {
            $endNode = $this->getRandomNode($this->nodesByIdentifier[$relationship->getEndNode()]);
            $this->createRelationship($relationship->getType(),
                $startNode->getId(),
                $startNode->getLabel(),
                $endNode->getId(),
                $endNode->getLabel(),
                $relationship->getProperties(),
                $seed);
        }

    }

    /**
     * @param RelationshipDefinition $relationship
     * @param null|int               $seed
     */
    public function process1To1Relationship(RelationshipDefinition $relationship, $seed)
    {
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
                    $this->createRelationship(
                        $relationship->getType(),
                        $startNode->getId(),
                        $startNode->getLabel(),
                        $endNode->getId(),
                        $endNode->getLabel(),
                        $relationship->getProperties(),
                        $seed
                    );
                    $usedEnds[] = $endPosition;
                    $usedEnds[] = $i;
                }
            }
            $i++;
        }
    }

    /**
     * @param RelationshipDefinition $relationship
     * @param null|int               $seed
     */
    public function processNToNRelationship(RelationshipDefinition $relationship, $seed)
    {
        $targetCount = $this->getTargetNodesCount($relationship, $this->nodesByIdentifier[$relationship->getEndNode()]);
        $startNodes = $this->nodesByIdentifier[$relationship->getStartNode()];
        $endNodes = $this->nodesByIdentifier[$relationship->getEndNode()];
        foreach ($startNodes as $i => $startNode) {
            $usedTargets = [];
            $x = 0;
            while ($x < $targetCount) {
                $endNodePosition = $this->getNotUsedNodePosition($usedTargets, $endNodes, $startNode);
                $endNode = $endNodes->get($endNodePosition);
                $this->createRelationship(
                    $relationship->getType(),
                    $startNode->getId(),
                    $startNode->getLabel(),
                    $endNode->getId(),
                    $endNode->getLabel(),
                    $relationship->getProperties(),
                    $seed
                );
                $usedTargets[$endNodePosition] = null;
                $x++;
            }
        }
    }

    /**
     * @param RelationshipDefinition $relationship
     * @param null|int               $seed
     */
    public function process1ToNRelationship(RelationshipDefinition $relationship, $seed)
    {
        $startNodes = $this->nodesByIdentifier[$relationship->getStartNode()];
        $endNodes = $this->nodesByIdentifier[$relationship->getEndNode()];
        $target = $this->calculateApproxTargetNodes($startNodes->count(), $endNodes->count());
        $maxIteration = 1 === $target ? $startNodes->count() : $startNodes->count() -1;
        if ($endNodes->count() > $startNodes->count()) {
            if ($endNodes->count() % $startNodes->count() === 0) {
                $maxIteration = $startNodes->count();
            }
        }
        $ec = $endNodes->count();
        $eci = 0;
        $ssi = 0;
        for ($s = 0; $s < $maxIteration; $s++) {
            for ($i = 0; $i < $target; $i++) {
                $startNode = $startNodes->get($s);
                $endNode = $endNodes->get($eci);
                $this->createRelationship(
                    $relationship->getType(),
                    $startNode->getId(),
                    $startNode->getLabel(),
                    $endNode->getId(),
                    $endNode->getLabel(),
                    $relationship->getProperties(),
                    $seed
                );
                $eci++;
            }
            $ssi++;
        }
        if ($ssi < $startNodes->count()) {
            $lastStartNode = $startNodes->get($ssi);
            for ($eci; $eci < $ec; $eci++) {
                $endNode = $endNodes->get($eci);
                $this->createRelationship(
                    $relationship->getType(),
                    $lastStartNode->getId(),
                    $lastStartNode->getLabel(),
                    $endNode->getId(),
                    $endNode->getLabel(),
                    $relationship->getProperties(),
                    $seed
                );
            }
        }
    }

    /**
     *
     * Guess the number of nodes to be associated to 1 node
     *
     * @param  int $startCount The count of relationship start nodes
     * @param  int $endCount   The count of relationship end nodes
     * @return int The targeted nodes count
     */
    public function calculateApproxTargetNodes($startCount, $endCount)
    {
        if (1 === $startCount) {
            return $endCount;
        }
        if ($startCount <= $endCount) {
            $diff = $endCount - $startCount;
            if (1 < $diff) {
                if ($endCount / $startCount === 2) {

                    return 2;
                }
                $target = (int) round($endCount/$startCount);
                print_r($target);

                return $target;
            }

            return 1;
        }

        $diff = (int) round($startCount/$endCount);
        $newStart = $startCount/$diff;

        return $this->calculateApproxTargetNodes($newStart, $endCount);
    }

    /**
     * @param  RelationshipDefinition $relationship
     * @param  ObjectCollection       $targetNodes
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

    /**
     * @param $type
     * @param $sourceId
     * @param $sourceLabel
     * @param $targetId
     * @param $targetLabel
     * @param $properties
     * @param $seed
     * @return Relationship
     */
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

        $this->relationships->add($relationship);
    }

    /**
     * @param  ObjectCollection $nodes
     * @return mixed|null
     */
    private function getRandomNode(ObjectCollection $nodes)
    {
        $max = $nodes->count();
        $i = rand(0, $max-1);

        return $nodes->get($i);
    }

    /**
     * @param $usedNodes
     * @param  ObjectCollection $collection
     * @param  null             $avoidSelf
     * @param  bool             $shuffle
     * @return int|null|string
     */
    private function getNotUsedNodePosition($usedNodes, ObjectCollection $collection, $avoidSelf = null, $shuffle = false)
    {
        foreach ($collection as $k => $n) {
            if (!array_key_exists($k, $usedNodes)) {
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

    /**
     * @param  Property                                 $property
     * @param $seed
     * @return string|\DateTime|int|float|array|boolean
     */
    private function getFakeData(Property $property, $seed)
    {
        $v = $this->faker->generate($property->getProvider(), $property->getArguments(), $seed, $property->isUnique());

        return $this->sanitizeValueForGraph($v);
    }

    /**
     * Sanitizes values for Neo4j primitives. E.g.: DateTime objects are converted to strings
     *
     * @param $v
     * @return string
     */
    private function sanitizeValueForGraph($v)
    {
        if ($v instanceof \DateTime) {
            return $v->format('Y-m-d H:i:s');
        }

        return $v;
    }

}
