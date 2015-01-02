<?php

namespace spec\Neoxygen\Neogen\Schema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Neoxygen\Neogen\Schema\NodeProperty;

class NodeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('person');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Schema\Node');
    }

    function it_should_have_an_identifier_on_init()
    {
        $this->getIdentifier()->shouldReturn('person');
    }

    function it_should_have_an_empty_collection_of_properties_on_init()
    {
        $this->getProperties()->shouldHaveType('Neoxygen\Neogen\Util\ObjectCollection');
    }

    function it_should_add_a_property_to_collection(NodeProperty $property)
    {
        $this->addProperty($property);
        $this->getProperties()->count()->shouldReturn(1);
    }

    function it_should_count_the_properties_collection(NodeProperty $property)
    {
        $this->addProperty($property);
        $this->getPropertiesCount()->shouldReturn(1);
    }

    function it_should_tell_whether_or_not_there_are_properties(NodeProperty $property)
    {
        $this->hasProperties()->shouldReturn(false);
        $this->addProperty($property);
        $this->hasProperties()->shouldReturn(true);
    }

    function it_should_return_the_indexed_properties(NodeProperty $property)
    {
        $property->isIndexed()->willReturn(true);
        $this->addProperty($property);
        $this->getIndexedProperties()->shouldBeArray();
        $this->getIndexedProperties()->shouldHaveCount(1);
    }

    function it_should_return_the_unique_properties(NodeProperty $property)
    {
        $this->getUniqueProperties()->shouldHaveCount(0);
        $property->isUnique()->willReturn(true);
        $this->addProperty($property);
        $this->getUniqueProperties()->shouldBeArray();
        $this->getUniqueProperties()->shouldHaveCount(1);
    }

    function it_should_not_duplicate_properties(NodeProperty $property)
    {
        $property->getName()->willReturn('first_name');
        $this->addProperty($property);
        $this->addProperty($property);
        $this->getProperties()->shouldHaveCount(1);
    }

    function it_should_not_have_labels_by_default()
    {
        $this->getLabels()->shouldHaveCount(0);
    }

    function it_should_be_possible_to_add_label()
    {
        $this->addLabel('Person');
        $this->getLabels()->shouldHaveCount(1);
    }

    function it_should_be_possible_to_add_multiple_labels()
    {
        $this->addLabels(array('Person', 'Adult', 'Speaker'));
        $this->getLabels()->shouldHaveCount(3);
        $this->hasLabel('Adult')->shouldReturn(true);
    }

    function it_should_not_duplicate_labels()
    {
        $this->addLabel('Person');
        $this->addLabels(array('Person', 'Adult'));
        $this->getLabels()->shouldHaveCount(2);
    }

    function it_should_tell_whether_or_not_the_node_has_a_specified_property(NodeProperty $property)
    {
        $property->getName()->willReturn('first_name');
        $this->addProperty($property);
        $this->hasProperty('first_name')->shouldReturn(true);
        $this->hasProperty('last_name')->shouldReturn(false);
    }

    function it_should_have_a_default_amount_of_1()
    {
        $this->getAmount()->shouldReturn(1);
        $this->setAmount(25);
        $this->getAmount()->shouldReturn(25);
    }
}
