<?php

namespace Neoxygen\Neogen\Tests\Processor;

use Neoxygen\Neogen\Processor\VertEdgeProcessor,
    Neoxygen\Neogen\Parser\CypherPattern;

class VertEdgeProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleNode()
    {
        $p = '(person:Person)';
        $schema = $this->getSchema($p);
        $processor = new VertEdgeProcessor();
        $processor->process($schema);
        $graph = $processor->getGraph();
        $this->assertCount(1, $graph['nodes']);
    }

    public function testSimplePattern()
    {
        $p = '(person:Person *10)-[:KNOWS *n..n]->(person)';
        $schema = $this->getSchema($p);
        $processor = new VertEdgeProcessor();
        $processor->process($schema);
        $graph = $processor->getGraph();
        $this->assertCount(10, $graph['nodes']);
        $this->assertTrue(count($graph['edges']) > 10);
    }

    private function getSchema($pattern)
    {
        $parser = new CypherPattern();
        $parser->parseCypher($pattern);

        return $parser->getSchema();
    }
}