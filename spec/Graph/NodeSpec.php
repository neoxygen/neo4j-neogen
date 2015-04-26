<?php

namespace spec\Neoxygen\Neogen\Graph;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NodeSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('1234-5678');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Graph\Node');
    }

    function it_should_have_an_id_on_construct()
    {
        $this->getId()->shouldReturn('1234-5678');
    }

    function it_should_have_an_empty_collection_of_properties()
    {
        $this->getProperties()->shouldHaveCount(0);
    }

    function it_should_add_property_to_collection()
    {
        $this->addProperty('name', 'Chris');
        $this->getProperties()->shouldHaveCount(1);
    }

    function it_should_return_or_not_it_has_the_given_property_name()
    {
        $this->addProperty('name', 'Chris');
        $this->hasProperty('name')->shouldReturn(true);
    }

    function it_should_return_if_the_node_has_properties()
    {
        $this->hasProperties()->shouldReturn(false);
        $this->addProperty('name', 'Chris');
        $this->hasProperties()->shouldReturn(true);
    }

    function it_should_add_labels_to_node()
    {
        $this->addLabels(array('Person', 'Adult'));
        $this->getLabels()->shouldHaveCount(2);
    }

    function it_should_return_the_first_label_by_default()
    {
        $this->addLabels(array('Person', 'Speaker'));
        $this->getLabel()->shouldReturn('Person');
    }

    function it_should_throw_exception_if_the_node_has_no_label()
    {
        $this->shouldThrow('RuntimeException')->duringGetLabel();
        $this->shouldThrow('RuntimeException')->duringGetLabels();
    }
}
