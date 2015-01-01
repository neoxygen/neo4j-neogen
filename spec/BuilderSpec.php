<?php

namespace spec\Neoxygen\Neogen;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BuilderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Builder');
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

    function it_should_add_seed_to_config()
    {
        $this->setSeed(1234);
        $this->getSeed()->shouldReturn(1234);
        $this->getConfiguration()->shouldHaveKey('seed');
    }

    function it_should_have_no_seed_by_default()
    {
        $this->getSeed()->shouldReturn(null);
    }
}
