<?php

namespace Neoxygen\Neogen\Schema;

class GraphSchemaBuilder
{
    /**
     *
     */
    public function __construct()
    {

    }

    /**
     * Build a graph definition based on the user parsed schema
     *
     * @param array $userSchema
     * @return \Neoxygen\Neogen\Schema\GraphSchema
     */
    public function buildGraph(array $userSchema)
    {
        $graphSchema = new GraphSchema();
        foreach ($userSchema['nodes'] as $id => $nodeInfo) {
            $node = $this->buildNode($id, $nodeInfo);
            $graphSchema->addNode($node);
        }
        foreach ($userSchema['relationships'] as $rel) {
            $relationship = $this->buildRelationship($rel);
            $graphSchema->addRelationship($relationship);
        }

        return $graphSchema;
    }

    /**
     * @param string $identifier
     * @param array $nodeInfo
     * @return Node
     */
    public function buildNode($identifier, array $nodeInfo)
    {
        $node = new Node($identifier);
        if (is_string($nodeInfo['labels'])) {
            $node->addLabel($nodeInfo['labels']);
        } elseif (is_array($nodeInfo['labels'])) {
            $node->addLabels($nodeInfo['labels']);
        }
        foreach ($nodeInfo['properties'] as $key => $info) {
            $property = $this->buildNodeProperty($key, $info);
            $node->addProperty($property);
        }

        return $node;
    }

    /**
     * @param string $name
     * @param string|array $info
     * @return NodeProperty
     */
    public function buildNodeProperty($name, $info)
    {
        $property = new NodeProperty($name);
        if (is_array($info)) {
            $property->setProvider($info['type'], $info['params']);
        } else {
            $property->setProvider($info);
        }

        return $property;
    }

    /**
     * Builds the relationship object based on user schema
     *
     * @param array $relInfo relationship info from user schema
     * @return Relationship
     */
    public function buildRelationship(array $relInfo)
    {
        $relationship = new Relationship($relInfo['start'], $relInfo['end'], $relInfo['type']);
        $relationship->setCardinality($relInfo['mode']);
        foreach ($relInfo['properties'] as $name => $info) {
            $property = $this->buildRelationshipProperty($name, $info);
            $relationship->addProperty($property);
        }

        return $relationship;
    }

    /**
     * @param string $name
     * @param string|array $info
     * @return RelationshipProperty
     */
    public function buildRelationshipProperty($name, $info)
    {
        $property = new RelationshipProperty($name);
        if (is_array($info)) {
            $property->setProvider($info['type'], $info['params']);
        } else {
            $property->setProvider($info);
        }

        return $property;
    }


}
