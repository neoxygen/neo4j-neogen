<?php

namespace spec\Neoxygen\Neogen\Schema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NodePropertySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('name');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Schema\NodeProperty');
    }

    function it_should_extend_base_property()
    {
        $this->shouldBeAnInstanceOf('Neoxygen\Neogen\Schema\Property');
    }

    function it_should_not_be_indexed_by_default()
    {
        $this->isIndexed()->shouldReturn(false);
    }

    function it_should_have_an_index_mutator()
    {
        $this->setIndexed();
        $this->isIndexed()->shouldReturn(true);
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

    function it_can_not_be_unique_and_indexed()
    {
        $this->setIndexed();
        $this->setUnique();
        $this->isIndexed()->shouldReturn(false);

        $this->setIndexed();
        $this->isUnique()->shouldReturn(false);
    }
}
