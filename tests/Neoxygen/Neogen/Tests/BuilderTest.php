<?php

namespace Neoxygen\Neogen\Tests;

use Neoxygen\Neogen\Neogen;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Neoxygen\Neogen\Neogen
     */
    protected $neogen;

    public function setup()
    {
        $neogen = Neogen::create()
            ->build();

        $this->neogen = $neogen;
    }

    public function testParserManagerIsRegistered()
    {
        $this->assertNotEmpty($this->neogen->getParserManager());
    }

    public function testYamlParserIsAvailable()
    {
        $this->assertInstanceOf('Neoxygen\Neogen\Parser\YamlFileParser', $this->neogen->getParser('yaml'));
    }

    public function testCypherPaserIsAvailable()
    {
        $this->assertInstanceOf('Neoxygen\Neogen\Parser\CypherPattern', $this->neogen->getParser('cypher'));
    }

    public function testGraphGeneratorIsAvailable()
    {
        $this->assertInstanceOf('Neoxygen\Neogen\GraphGenerator\Generator', $this->neogen->getGraphGenerator());
    }
}

