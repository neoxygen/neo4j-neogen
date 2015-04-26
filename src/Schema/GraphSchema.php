<?php

namespace Neoxygen\Neogen\Schema;

use Neoxygen\Neogen\Util\ObjectCollection;
use Neoxygen\Neogen\Schema\Node,
    Neoxygen\Neogen\Schema\Relationship,
    Neoxygen\Neogen\Exception\SchemaDefinitionException;

class GraphSchema
{
    /**
     * @var ObjectCollection[\Neoxygen\Neogen\Schema\Node] The schema nodes collection
     */
    protected $nodes;

    /**
     * @var ObjectCollection[\Neoxygen\Neogen\Schema\Relationship] The schema relationships collection
     */
    protected $relationships;

    /**
     *
     */
    public function __construct()
    {
        $this->nodes = new ObjectCollection();
        $this->relationships = new ObjectCollection();
    }

    /**
     * @return ObjectCollection[\Neoxygen\Neogen\Schema\Node]
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * @return ObjectCollection[\Neoxygen\Neogen\Schema\Relationship]
     */
    public function getRelationships()
    {
        return $this->relationships;
    }

    /**
     * Adds a node to the nodes collection
     *
     * @param  Node                      $node
     * @return bool
     * @throws SchemaDefinitionException
     */
    public function addNode(Node $node)
    {
        foreach ($this->nodes as $n) {
            if ($n->getIdentifier() === $node->getIdentifier()) {
                throw new SchemaDefinitionException(sprintf('The node with Identifier "%s" has already been declared', $node->getIdentifier()));
            }
        }

        return $this->nodes->add($node);
    }

    /**
     * Adds a relationship to the relationship collection
     *
     * @param  Relationship              $relationship
     * @return bool
     * @throws SchemaDefinitionException
     */
    public function addRelationship(Relationship $relationship)
    {
        foreach ($this->relationships as $rel) {
            if ($rel->getType() === $relationship->getType() &&
            $rel->getStartNode() === $relationship->getStartNode() &&
            $rel->getEndNode() === $relationship->getEndNode()) {
                throw new SchemaDefinitionException(sprintf('There is already a relationship declared with TYPE "%s" and STARTNODE "%s" and ENDNODE "%s"',
                    $relationship->getType(), $relationship->getStartNode(), $relationship->getEndNode()));
            }
        }

        return $this->relationships->add($relationship);
    }

    public function toArray()
    {
        return array(
            'nodes' => $this->nodes->getValues(),
            'relationships' => $this->relationships->getValues()
        );
    }
}
