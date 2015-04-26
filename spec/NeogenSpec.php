<?php

namespace spec\Neoxygen\Neogen;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NeogenSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('create', array());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Neogen');
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

    function it_should_freeze_the_container_after_build()
    {
        $this->build();
        $this->getServiceContainer()->isFrozen()->shouldReturn(true);
    }

    function it_should_throw_exception_when_accessing_service_and_container_is_not_frozen()
    {
        $this->shouldThrow('RuntimeException')->duringGetParserManager();
    }

    function it_should_return_the_parser_manager()
    {
        $this->build();
        $this->getParserManager()->shouldHaveType('Neoxygen\Neogen\Parser\ParserManager');
    }
}
