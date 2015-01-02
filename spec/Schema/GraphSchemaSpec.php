<?php

namespace spec\Neoxygen\Neogen\Schema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Neoxygen\Neogen\Schema\Node,
    Neoxygen\Neogen\Schema\Relationship;

class GraphSchemaSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Schema\GraphSchema');
    }

    function it_should_have_an_empty_collection_of_nodes_by_default()
    {
        $this->getNodes()->shouldHaveType('Neoxygen\Neogen\Util\ObjectCollection');
        $this->getNodes()->shouldHaveCount(0);
    }

    function it_should_have_an_empty_collection_of_relationships_by_default()
    {
        $this->getRelationships()->shouldHaveType('Neoxygen\Neogen\Util\ObjectCollection');
        $this->getRelationships()->shouldHaveCount(0);
    }

    function it_should_add_node_to_collection(Node $node)
    {
        $this->addNode($node);
        $this->getNodes()->shouldHaveCount(1);
    }

    function it_should_add_relationships_to_collection(Relationship $relationship)
    {
        $this->addRelationship($relationship);
        $this->getRelationships()->shouldHaveCount(1);
    }

    function it_should_throw_error_when_adding_nodes_with_same_identifier(Node $node)
    {
        $node->getIdentifier()->willReturn('person');
        $this->addNode($node);
        $this->shouldThrow('Neoxygen\Neogen\Exception\SchemaDefinitionException')->duringAddNode($node);
    }

    function it_should_throw_error_when_creating_duplicated_relationships(Relationship $relationship)
    {
        $relationship->getType()->willReturn('RELATES');
        $relationship->getStartNode()->willReturn('person');
        $relationship->getEndNode()->willReturn('company');
        $this->addRelationship($relationship);
        $this->shouldThrow('Neoxygen\Neogen\Exception\SchemaDefinitionException')->duringAddRelationship($relationship);
    }
}
