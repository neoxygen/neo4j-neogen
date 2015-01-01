<?php

namespace spec\Neoxygen\Neogen\Parser;

use Neoxygen\Neogen\Parser\YamlFile;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ParserManagerSpec extends ObjectBehavior
{
    function let($parser)
    {
        $parser->beADoubleOf('Neoxygen\Neogen\Parser\YamlFile');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Parser\ParserManager');
    }

    function it_should_not_have_parsers_register_on_init()
    {
        $this->getParsers()->shouldHaveCount(0);
    }

    function it_should_add_a_parser_to_the_stack($parser)
    {
        $parser->getName()->willReturn('YamlParser');
        $this->registerParser($parser);
        $this->getParsers()->shouldHaveCount(1);
        $this->getParsers()->shouldHaveKey('YamlParser');
    }

    function it_should_be_possible_to_know_if_at_least_one_parser_is_registered($parser)
    {
        $this->hasParsers()->shouldReturn(false);
        $this->registerParser($parser);
        $this->hasParsers()->shouldReturn(true);
    }

    function it_should_return_bool_when_asking_if_parser_is_registered($parser)
    {
        $parser->getName()->willReturn('YamlParser');
        $this->hasParser('YamlParser')->shouldReturn(false);
        $this->registerParser($parser);
        $this->hasParser('YamlParser')->shouldReturn(true);
    }

    function it_should_return_the_desired_parser($parser)
    {
        $parser->getName()->willReturn('YamlParser');
        $this->registerParser($parser);
        $this->getParser('YamlParser')->shouldReturn($parser);
    }

    function it_should_throw_exception_when_parser_does_not_exist()
    {
        $this->shouldThrow('Neoxygen\Neogen\Exception\ParserNotFoundException')->duringGetParser('YamlParser');
    }
}
