<?php

namespace Neoxygen\Neogen\Tests\Parser;

use Neoxygen\Neogen\Parser\CypherPattern;

class CypherPatternTest extends \PHPUnit_Framework_TestCase
{
    protected $nodePattern;

    protected $edgePattern;

    protected $parser;

    public function testParserAddNodeToStackWithProps()
    {
        $cypher = '(p:Post {firstname:~, weight: {numberBetween: [10,100]}}*35)';
        $parser = new CypherPattern();
        $this->assertEmpty($parser->getNodes());

        $parser->parseCypher($cypher);
        $nodes = $parser->getNodes();
        $this->assertCount(1, $nodes);
    }

    public function testParserProcessEdges()
    {
        $cypher = '(p:Person {firstname: ~ } *10)-[:WORKS_AT *n..1]->(c:Company *20)
        (s:Skill *40)
        (p)-[:HAS { since: { dateTimeBetween: ["-10 years","-5 years"]}} *n..n]->(s)';
        $parser = new CypherPattern();
        $parser->parseCypher($cypher);
        $schema = $parser->getSchema();

        //$this->assertArrayHasKey('type', $schema['relationships'][1]['properties']['since']);
        $this->assertArrayHasKey('firstname', $schema['nodes'][0]['properties']);

        $cypher = '(p:Person *20)-[:WRITE *1..n]->(post:Post *35)
(p)-[:KNOWS *n..n]->(p)
(p)-[:COMMENTED_ON *n..n]->(post)';

        $parser->parseCypher($cypher);
    }

    public function testLineParser()
    {
        $p = '(p:Person {firstname: firstName, lastname: lastName} *10)-[:KNOWS *n..n]->(p)';
        $parser = new CypherPattern();
        $parts = $parser->parseLine($p);

        $this->assertEquals('(p:Person {firstname: firstName, lastname: lastName} *10)', $parts[0]);
        $this->assertEquals('-[:KNOWS *n..n]->', $parts[1]);
        $this->assertEquals('(p)', $parts[2]);

        $p = '(p:Person {firstname: firstName, lastname: lastName} *10)-[:KNOWS *1..1]->(p)';
        $parser = new CypherPattern();
        $parts = $parser->parseLine($p);

        $this->assertEquals('(p:Person {firstname: firstName, lastname: lastName} *10)', $parts[0]);
        $this->assertEquals('-[:KNOWS *1..1]->', $parts[1]);
        $this->assertEquals('(p)', $parts[2]);

        $p = '(p:Person {firstname: firstName, lastname: lastName} *10)-[:KNOWS *n..10]->(p)';
        $parser = new CypherPattern();
        $parts = $parser->parseLine($p);

        $this->assertEquals('(p:Person {firstname: firstName, lastname: lastName} *10)', $parts[0]);
        $this->assertEquals('-[:KNOWS *n..10]->', $parts[1]);
        $this->assertEquals('(p)', $parts[2]);
    }

    public function testMultiplePatternIsParsed()
    {
        $cypher = '(p:Person {name:lastName} *15)-[:WORKS_AT *n..1]->(s:Startup {name:company} *6)-[:IN_MARKET *n..1]->(m:Market {name:catchPhrase} *2)';
        $parser = new CypherPattern();
        $parser->parseCypher($cypher);
        $schema = $parser->getSchema();
        $this->assertCount(2, $schema['relationships']);
        $this->assertArrayHasKey('name', $schema['nodes'][0]['properties']);
        $this->assertEquals('WORKS_AT', $schema['relationships'][0]['type']);
        $this->assertEquals('IN_MARKET', $schema['relationships'][1]['type']);
    }

    public function testNodePatternInfo()
    {
        $this->parser = new CypherPattern();
        $this->nodePattern = $this->parser->getNodePattern();
        $this->edgePattern = $this->parser->getEdgePattern();

        $cypher = '(post)';
        $info = $this->getNodeInfo($cypher);
        $this->assertEquals('post', $info['identifier']);
        $this->assertNull($info['label']);
        $this->assertNull($info['properties']);
        $this->assertNull($info['count']);

        $cypher = '(p:Post)';
        $this->assertNodeInfo($cypher, 'p', 'Post');

        $cypher = '(p:Post *35)';
        $this->assertNodeInfo($cypher, 'p', 'Post', null, 35);

        $cypher = '(p:Post*35)';
        $this->assertNodeInfo($cypher, 'p', 'Post', null, 35);

        $cypher = '(p:Post *35 )';
        $this->assertNodeInfo($cypher, 'p', 'Post', null, 35);

        $cypher = '(:Post)';
        $this->assertNodeInfo($cypher, null, 'Post');

        $cypher = '(:Post *35)';
        $this->assertNodeInfo($cypher, null, 'Post', null, 35);

        $cypher = '(p:Post {firstname, weight: {numberBetween: [10,100]}})';
        $this->assertNodeInfo($cypher, 'p', 'Post', '{firstname, weight: {numberBetween: [10,100]}}');

        $cypher = '(:Post {firstname, weight: {numberBetween: [10,100]}})';
        $this->assertNodeInfo($cypher, null, 'Post', '{firstname, weight: {numberBetween: [10,100]}}');

        $cypher = '(p:Post {firstname, weight: {numberBetween: [10,100]}} *35)';
        $this->assertNodeInfo($cypher, 'p', 'Post', '{firstname, weight: {numberBetween: [10,100]}}', 35);

        $cypher = '(p:Post {firstname:~, weight: {numberBetween: [10,100]}}*35)';
        $this->assertNodeInfo($cypher, 'p', 'Post', '{firstname:~, weight: {numberBetween: [10,100]}}', 35);

    }

    public function testEdgePatternInfo()
    {
        $this->parser = new CypherPattern();
        $this->nodePattern = $this->parser->getNodePattern();
        $this->edgePattern = $this->parser->getEdgePattern();

        $cypher = '-[:WORKS_AT *n..1]->';
        $this->assertEdgeInfo($cypher, 'WORKS_AT', 'OUT', 'n..1');

        $cypher = '-[:WORKS_AT {since: {dateTimeBetween: ["-10 years", "-5 years"]}} *n..1]->';
        $this->assertEdgeInfo($cypher, 'WORKS_AT', 'OUT', 'n..1', '{since: {dateTimeBetween: ["-10 years", "-5 years"]}}');

        $cypher = '<-[:COMMENT *n..n]-';
        $this->assertEdgeInfo($cypher, 'COMMENT', 'IN', 'n..n');

        $cypher = '-[:WORKS_AT*n..1]->';
        $this->assertEdgeInfo($cypher, 'WORKS_AT', 'OUT', 'n..1');

        $cypher = '-[:WORKS_AT {since: {dateTimeBetween: ["-10 years", "-5 years"]}}*n..1]->';
        $this->assertEdgeInfo($cypher, 'WORKS_AT', 'OUT', 'n..1', '{since: {dateTimeBetween: ["-10 years", "-5 years"]}}');
    }

    private function assertNodeInfo($cypher, $id = null, $label = null, $props = null, $count = null)
    {
        $info = $this->getNodeInfo($cypher);
        $this->assertEquals($id, $info['identifier']);
        $this->assertEquals($label, $info['label']);
        $this->assertEquals($props, $info['properties']);
        $this->assertEquals($count, $info['count']);
    }

    private function assertEdgeInfo($cypher, $type, $direction, $cardinality, $props = null )
    {
        $info = $this->getEdgeInfo($cypher);
        $this->assertEquals($type, $info['type']);
        $this->assertEquals($direction, $info['direction']);
        $this->assertEquals($props, $info['properties']);
        $this->assertEquals($cardinality, $info['cardinality']);
    }

    private function matchPattern($p, $text)
    {
        preg_match($p, $text, $output);

        return $output;
    }

    private function getNodeInfo($cypher)
    {
        $match = $this->matchPattern($this->nodePattern, $cypher);
        $info = $this->parser->getNodePatternInfo($match, $cypher);

        return $info;
    }

    private function getEdgeInfo($cypher)
    {
        $match = $this->matchPattern($this->edgePattern, $cypher);
        $info = $this->parser->getEdgePatternInfo($match);

        return $info;
    }
}