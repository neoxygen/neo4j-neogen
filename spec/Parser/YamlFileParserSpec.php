<?php

namespace spec\Neoxygen\Neogen\Parser;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class YamlFileParserSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Neoxygen\Neogen\Parser\YamlFileParser');
    }

    function it_should_implement_Parser_Interface()
    {
        $this->shouldImplement('Neoxygen\Neogen\Parser\ParserInterface');
    }

    function it_should_have_a_filesystem_instance_on_init()
    {
        $this->getFS()->shouldHaveType('Symfony\Component\Filesystem\Filesystem');
    }

    function it_should_throw_error_if_schema_file_not_found()
    {
        $this->shouldThrow('Neoxygen\Neogen\Exception\SchemaDefinitionException')->duringGetSchemaFileContent('/tmp/file.yml');
    }

    function it_should_load_yaml_schema_file()
    {
        $this->getSchemaFileContent(__DIR__.'/_schema1.yml')->shouldBeArray();
    }

    function it_should_throw_exception_if_yaml_is_not_valid()
    {
        $this->shouldThrow('Neoxygen\Neogen\Exception\SchemaDefinitionException')->duringGetSchemaFileContent(__DIR__.'/invalid-schema.yml');
    }

    function it_should_return_a_schema_array()
    {
        $this->parse(__DIR__.'/_schema1.yml')->shouldBeArray();
    }
}
