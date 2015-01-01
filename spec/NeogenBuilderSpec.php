<?php

namespace spec\Neoxygen\Neogen;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NeogenBuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\NeogenBuilder');
    }

    function it_has_a_di_container_by_default()
    {
        $this->getServiceContainer()->shouldNotBeNull();
    }

    function it_has_a_default_empty_config()
    {
        $this->getConfiguration()->shouldHaveCount(0);
    }

    function it_should_return_a_neogen_instance_on_build()
    {
        $this->build()->shouldHaveType('Neoxygen\Neogen\Neogen');
    }

    function it_should_have_a_non_frozen_container_by_default()
    {
        $this->getServiceContainer()->isFrozen()->shouldReturn(false);
    }
}
