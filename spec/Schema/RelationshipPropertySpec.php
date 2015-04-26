<?php

namespace spec\Neoxygen\Neogen\Schema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RelationshipPropertySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('since');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Schema\RelationshipProperty');
    }

    function it_should_extend_base_property()
    {
        $this->shouldBeAnInstanceOf('Neoxygen\Neogen\Schema\Property');
    }

    function it_should_not_be_unique_by_default()
    {
        $this->isUnique()->shouldReturn(false);
    }

    function it_should_have_a_unique_mutator()
    {
        $this->setUnique();
        $this->isUnique()->shouldReturn(true);
    }
}
