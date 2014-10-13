<?php

namespace Neoxygen\Neogen\Tests;

use Neoxygen\Neogen\Neogen,
    Neoxygen\Neogen\Converter\CypherStatementsConverter;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testPatternProcessing1()
    {
        $p = '(p:Product *1)
(cc:ComponentCategory *20)-[:PART_OF {quantity: {randomNumber: [3]}} *n..1]->(p)
(csc:ComponentSubCategory *20)-[:PART_OF {quantity: {randomNumber: [3]}} *n..1]->(cc)
(c:Component {price: {randomNumber: [2]}} *20)-[:PART_OF {quantity: {randomNumber: [3]}} *n..1]->(csc)';

        $gen = new Neogen();
        $schema = $gen->generateGraphFromCypher($p);
        $converter = new CypherStatementsConverter();
        $converter->convert($schema);

        $edgesStatements = $converter->getEdgeStatements();
        $this->assertCount(7, $edgesStatements);
        $this->assertCount(4, $converter->getNodeStatements());


    }
}