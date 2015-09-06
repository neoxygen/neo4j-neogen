<?php

namespace GraphAware\Neogen\Tests\Unit\Parser;

use GraphAware\Neogen\Parser\CypherPattern;
use GraphAware\Neogen\Exception\ParseException;

/**
 * Class CypherPatternParserTest
 * @package GraphAware\Neogen\Tests\Unit\Parser
 *
 * @group parser
 * @group unit
 */
class CypherPatternParserTest extends \PHPUnit_Framework_TestCase
{
    public function testPreFormatPatternForLineBreaksAndConcat()
    {
        $parser = new CypherPattern();
        $pattern = '(a:Node)(b:Node)';
        $cleaned = $parser->preFormatPattern($pattern);
        $this->assertEquals("(a:Node)\n(b:Node)", $cleaned);
    }

    public function testCommentedLinesAreStriped()
    {
        $parser = new CypherPattern();
        $pattern = "//(a:Node)-[:RELATES_TO]->(b)\n(b:Node)";
        $this->assertEquals("(b:Node)", $parser->preFormatPattern($pattern));
    }

    public function testLineIsParsedAndSplitted()
    {
        $parser = new CypherPattern();
        $pattern = "(a:Node)-[:RELATES_TO *n..n]->(b:Node)";
        $extract = $parser->parseLine($pattern);
        $this->assertCount(3, $extract);
    }

    public function testMatchNodePattern()
    {
        $parser = new CypherPattern();
        $str = '(a:Node:#SuperNode:Person {uuid: {randomNumber:[0,20]}} *100)';
        $definition = $parser->matchPattern($str);

        $this->assertEquals("a", $definition->getIdentifier());
        $this->assertCount(3, $definition->getLabels());
        $this->assertCount(1, $definition->getModels());
    }

    public function testIdentifierIsRequired()
    {
        $this->setExpectedException(ParseException::class);
        $parser = new CypherPattern();
        $str = '(:Node *1)';
        $parser->matchPattern($str);
    }

    public function testAtLeastOneLabelIsRequired()
    {
        $this->setExpectedException(ParseException::class);
        $parser = new CypherPattern();
        $str = '(a *1)';
        $parser->matchPattern($str);
    }

    public function testIndexMarkIsTaken()
    {
        $parser = new CypherPattern();
        $str = '(a:Node {?uuid: uuid})';
        $definition = $parser->matchPattern($str);

        $this->assertCount(1, $definition->getProperties());
        foreach ($definition->getProperties() as $prop) {
            $this->assertTrue($prop->isIndexed());
        }
    }

    public function testUniqueConstraintMarkIsTaken()
    {
        $parser = new CypherPattern();
        $str = '(a:Node {!uuid: uuid})';
        $definition = $parser->matchPattern($str);

        $this->assertCount(1, $definition->getProperties());
        foreach ($definition->getProperties() as $prop) {
            $this->assertTrue($prop->isUnique());
        }
    }

    public function testOtherMarkersThrowErrors()
    {
        $str = '(w:Node {;uuid: uuid})';
        $parser = new CypherPattern();
        $this->setExpectedException(ParseException::class);
        $parser->matchPattern($str);
    }

    public function testTacksAreNotAllowedInPropertyKeys()
    {
        $str = '(w:Node {uuid-v1: uuid})';
        $parser = new CypherPattern();
        $this->setExpectedException(ParseException::class);
        $parser->matchPattern($str);
    }
}