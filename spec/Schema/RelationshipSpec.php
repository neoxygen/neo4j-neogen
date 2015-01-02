<?php

namespace spec\Neoxygen\Neogen\Schema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Neoxygen\Neogen\Schema\RelationshipProperty;

class RelationshipSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('person', 'company', 'RELATES');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Schema\Relationship');
    }

    function it_should_have_a_type_by_default()
    {
        $this->getType()->shouldReturn('RELATES');
    }

    function it_should_have_an_empty_collection_of_properties()
    {
        $this->getProperties()->shouldHaveType('Neoxygen\Neogen\Util\ObjectCollection');
        $this->getProperties()->shouldHaveCount(0);
    }

    function it_should_add_a_property_to_the_collection(RelationshipProperty $property)
    {
        $this->addProperty($property);
        $this->getProperties()->shouldHaveCount(1);
    }

    function it_should_return_whether_or_not_there_are_properties(RelationshipProperty $property)
    {
        $this->hasProperties()->shouldReturn(false);
        $this->addProperty($property);
        $this->hasProperties()->shouldReturn(true);
    }

    function it_should_not_duplicate_properties(RelationshipProperty $property)
    {
        $property->getName()->willReturn('since');
        $this->addProperty($property);
        $property->getName()->willReturn('since');
        $this->addProperty($property);
        $this->getProperties()->shouldHaveCount(1);
    }

    function it_should_tell_if_the_relationship_has_the_specified_property_name(RelationshipProperty $property)
    {
        $property->getName()->willReturn('since');
        $this->addProperty($property);
        $this->hasProperty('since')->shouldReturn(true);
        $this->hasProperty('weight')->shouldReturn(false);
    }

    function it_should_have_a_start_and_end_node_by_default()
    {
        $this->getStartNode()->shouldReturn('person');
        $this->getEndNode()->shouldReturn('company');
    }

    function it_should_not_have_a_default_cardinality()
    {
        $this->getCardinality()->shouldBeNull();
        $this->setCardinality('1..1');
        $this->getCardinality()->shouldReturn('1..1');
    }
}
