<?php

namespace spec\Neoxygen\Neogen\Schema;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PropertySpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('prop');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Schema\Property');
    }

    function it_should_have_a_name_on_construct()
    {
        $this->getName()->shouldNotBeNull();
    }

    function it_should_not_have_a_provider_by_default()
    {
        $this->getProvider()->shouldBeNull();
    }

    function it_should_have_a_provider_mutator()
    {
        $this->setProvider('unixTime');
        $this->getProvider()->shouldReturn('unixTime');
    }

    function it_should_accept_provider_arguments()
    {
        $this->setProvider('numberBetween', array(1, 5));
        $this->getArguments()->shouldBeArray();
    }

    function it_should_return_bool_if_it_has_arguments_or_not()
    {
        $this->hasArguments()->shouldReturn(false);
        $this->setProvider('numberBetween', array(1,5));
        $this->hasArguments()->shouldReturn(true);
    }
}
