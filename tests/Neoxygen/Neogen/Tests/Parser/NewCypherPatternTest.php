<?php

namespace Neoxygen\Neogen\Tests\Parser;

use Neoxygen\Neogen\Neogen;
use Neoxygen\Neogen\Parser\CypherPattern;

class NewCypherPatternTest extends \PHPUnit_Framework_TestCase
{
    private $schemaException = 'Neoxygen\\Neogen\\Exception\\SchemaException';

    public function testParseSimpleNode()
    {
        $parser = new CypherPattern();
        $p = '(person:Person *10)';
        $parser->parseCypher($p);
        $schema = $parser->getSchema();
        $this->assertArrayHasKey('person', $schema->getNodes());
        $this->assertCount(1, $schema->getNodes()['person']['labels']);
        $this->assertEquals(10, $schema->getNodes()['person']['count']);
    }

    public function testTwoSimpleNodes()
    {
        $parser = new CypherPattern();
        $p = '(person:Person)
        (chart:Chart)';
        $parser->parseCypher($p);
        $schema = $parser->getSchema();
        $this->assertArrayHasKey('person', $schema->getNodes());
        $this->assertArrayHasKey('chart', $schema->getNodes());
        $this->assertCount(1, $schema->getNodes()['person']['labels']);
        $this->assertCount(1, $schema->getNodes()['chart']['labels']);
    }

    public function testSimpleNodeWithProperties()
    {
        $p = '(person:Person {id:uuid})';
        $parser = new CypherPattern();
        $parser->parseCypher($p);
        $schema = $parser->getSchema();
        $this->assertArrayHasKey('person', $schema->getNodes());
        $this->assertCount(1, $schema->getNodes()['person']['labels']);
        $this->assertCount(1, $schema->getNodes()['person']['properties']);
    }

    public function testSimpleEdge()
    {
        $p = '(person:Person {id:uuid})-[:WORKS_AT *n..1]->(company:Company)';
        $parser = new CypherPattern();
        $parser->parseCypher($p);
        $schema = $parser->getSchema();
        $this->assertArrayHasKey('person', $schema->getNodes());
        $this->assertCount(1, $schema->getNodes()['person']['labels']);
        $this->assertCount(1, $schema->getNodes()['person']['properties']);
        $this->assertArrayHasKey('company', $schema->getNodes());
        $this->assertCount(1, $schema->getEdges());
        $this->assertEquals('WORKS_AT', $schema->getEdges()[0]['type']);
        $this->assertEquals('person', $schema->getEdges()[0]['start']);
        $this->assertEquals('company', $schema->getEdges()[0]['end']);
    }

    public function testSimpleEdgeInverse()
    {
        $p = '(person:Person {id:uuid})<-[:WORKS_AT *n..1]-(company:Company)';
        $parser = new CypherPattern();
        $parser->parseCypher($p);
        $schema = $parser->getSchema();
        $this->assertArrayHasKey('person', $schema->getNodes());
        $this->assertCount(1, $schema->getNodes()['person']['labels']);
        $this->assertCount(1, $schema->getNodes()['person']['properties']);
        $this->assertArrayHasKey('company', $schema->getNodes());
        $this->assertCount(1, $schema->getEdges());
        $this->assertEquals('WORKS_AT', $schema->getEdges()[0]['type']);
        $this->assertEquals('person', $schema->getEdges()[0]['end']);
        $this->assertEquals('company', $schema->getEdges()[0]['start']);
    }

    public function testReuseOfIdentifier()
    {
        $p = '(person:Person {id:uuid} *10)-[:WORKS_AT *n..1]->(company:Company *5)
        (person)-[:KNOWS *n..n]->(person)';
        $parser = new CypherPattern();
        $parser->parseCypher($p);
        $schema = $parser->getSchema();
        $this->assertArrayHasKey('person', $schema->getNodes());
        $this->assertCount(1, $schema->getNodes()['person']['labels']);
        $this->assertCount(1, $schema->getNodes()['person']['properties']);
        $this->assertArrayHasKey('company', $schema->getNodes());
        $this->assertCount(2, $schema->getEdges());
        $this->assertCount(2, $schema->getNodes());
    }

    public function testModelLayerIdentifier()
    {
        $p = '(person:#Person {id:uuid} *10)-[:WORKS_AT *n..1]->(company:#Company *5)
        (person)-[:KNOWS *n..n]->(person)';
        $label = '#Person';
        $parser = new CypherPattern();
        $parser->parseCypher($p);
        $schema = $parser->getSchema();
        $this->assertCount(1, $schema->getNodes()['person']['labels']);
        $this->assertCount(1, $schema->getNodes()['person']['properties']);
        $this->assertArrayHasKey('company', $schema->getNodes());
        $this->assertCount(2, $schema->getEdges());
        $this->assertCount(2, $schema->getNodes());
        $this->assertEquals('Company', $schema->getNodes()['company']['models'][0]);
    }

    public function testErrorWhenNoIdentifier()
    {
        $p = '(:Person)';
        $this->setExpectedException($this->schemaException);
        $parser = new CypherPattern();
        $parser->parseCypher($p);
    }

    public function testErrorWhenNoCardinality()
    {
        $parser = new CypherPattern();
        $p = '(p:Person)-[:WORKS_AT]->(p)';
        $this->setExpectedException($this->schemaException);
        $parser->parseCypher($p);
    }

    public function testErrorWhenNoEdgeType()
    {
        $parser = new CypherPattern();
        $p = '(p:Person)-[ *n..1]->(p)';
        $this->setExpectedException($this->schemaException);
        $parser->parseCypher($p);
    }

    public function testErrorWhenBadPattern()
    {
        $parser = new CypherPattern();
        $p = ':Person *10)';
        $this->setExpectedException($this->schemaException);
        $parser->parseCypher($p);
    }

    public function testErrorWhenIdentifierWasNotDeclared()
    {
        $p = '(post)-[:WRITTEN_BY *n..1]->(user:User)';
        $parser = new CypherPattern();
        $this->setExpectedException($this->schemaException);
        $parser->parseCypher($p);
    }

    public function testNodeWithCustomProperty()
    {
        $p = '(d:Date {t: {numberBetween:[0,100]} })';
        $gen = new Neogen();
        $gen->generateGraphFromCypher($p);
    }

    public function testIbanGenerator()
    {
        $p = '(account:Account {iban___de_DE: bankAccountNumber }*20)';
        $gen = new Neogen();
        $gen->generateGraphFromCypher($p);
    }
}