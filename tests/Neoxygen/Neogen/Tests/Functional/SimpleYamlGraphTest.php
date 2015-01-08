<?php

use Neoxygen\Neogen\Neogen;

class SimpleYamlGraphTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Neoxygen\Neogen\Neogen
     */
    protected $neogen;

    public function setup()
    {
        $this->neogen = Neogen::create()
            ->build();
    }

    public function testYamlSchemaIsParsed()
    {
        $neogen = $this->neogen;
        $path = __DIR__.'/simple_schema.yml';
        $parser = $neogen->getParser('yaml');
        $userSchema = $parser->parse($path);

        $this->assertArrayHasKey('nodes', $userSchema);
        $this->assertArrayHasKey('person', $userSchema['nodes']);
        $this->assertArrayHasKey('labels', $userSchema['nodes']['person']);
        $this->assertArrayHasKey('birth', $userSchema['nodes']['person']['properties']);
    }

    public function testGraphIsGenerated()
    {
        $neogen = $this->neogen;
        $path = __DIR__.'/simple_schema.yml';
        $parser = $neogen->getParser('yaml');
        $userSchema = $parser->parse($path);
        $graph = $neogen->generateGraph($userSchema);

        $this->assertInstanceOf('Neoxygen\Neogen\Graph\Graph', $graph);
        $this->assertEquals(3, $graph->getNodes()->count());
        foreach ($graph->getNodes() as $node) {
            $this->assertEquals('Person', $node->getLabel());
        }
    }
}