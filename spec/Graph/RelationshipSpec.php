<?php

namespace spec\Neoxygen\Neogen\Graph;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RelationshipSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Graph\Relationship');
    }

    function it_should_have_a_type()
    {
        $this->getType()->shouldBeNull();
        $this->setType('RELATES_TO');
        $this->getType()->shouldReturn('RELATES_TO');
    }

    function it_should_have_a_source_id()
    {
        $this->getSourceId()->shouldBeNull();
        $this->setSourceId('1234');
        $this->getSourceId()->shouldReturn('1234');
    }

    function it_should_have_a_target_id()
    {
        $this->getTargetId()->shouldBeNull();
        $this->setTargetId('1234');
        $this->getTargetId()->shouldReturn('1234');
    }

    function it_should_have_a_source_label()
    {
        $this->getSourceLabel()->shouldBeNull();
        $this->setSourceLabel('User');
        $this->getSourceLabel()->shouldReturn('User');
    }

    function it_should_have_a_target_label()
    {
        $this->getTargetLabel()->shouldBeNull();
        $this->setTargetLabel('User');
        $this->getTargetLabel()->shouldReturn('User');
    }

    function it_should_have_an_emtpy_collection_of_properties()
    {
        $this->getProperties()->shouldHaveCount(0);
    }

    function it_should_set_properties()
    {
        $this->addProperty('name', 'Chris');
        $this->getProperties()->shouldHaveCount(1);
    }

    function it_should_return_specific_property()
    {
        $this->addProperty('name', 'Chris');
        $this->getProperty('name')->shouldReturn('Chris');
    }

    function it_should_return_bool_if_properties()
    {
        $this->hasProperties()->shouldReturn(false);
        $this->addProperty('name', 'Chris');
        $this->hasProperties()->shouldReturn(true);
    }
}
