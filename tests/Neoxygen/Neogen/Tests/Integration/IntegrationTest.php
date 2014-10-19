<?php

namespace Neoxygen\Neogen\Tests\Integration;

use Neoxygen\Neogen\Neogen,
    Neoxygen\Neogen\Converter\CypherStatementsConverter,
    Neoxygen\NeoClient\Client,
    Neoxygen\NeoClient\Formatter\ResponseFormatter;
use Neoxygen\Neogen\Parser\CypherPattern;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    protected $client;

    public function testBasicPattern()
    {
        $pattern = '(p:Person *35)';
        $this->loadGraphInDB($pattern);

        $query = 'MATCH (n) RETURN n';
        $result = $this->sendQuery($query);

        $this->assertEquals(35, $result->getNodesCount());
        $this->assertEquals('Person', $result->getSingleNode()->getLabel());
    }

    public function testBasicPatternWithSimpleProps()
    {
        $pattern = '(p:Person {firstname: firstName, digit: randomDigit} *10)';
        $this->loadGraphInDB($pattern);

        $q = 'MATCH (n) RETURN n';
        $result = $this->sendQuery($q);

        $this->assertEquals(10, $result->getNodesCount());
        $this->assertEquals('Person', $result->getSingleNode()->getLabel());
        $this->assertInternalType('integer', $result->getSingleNode()->getProperty('digit'));
        $this->assertTrue($result->getSingleNode()->hasProperty('firstname'));
    }

    public function testBasicPatternWithCustomProps()
    {
        $pattern = '(p:Person {firstname: firstName, age: {numberBetween: [18, 50]}} *10)';
        $this->loadGraphInDB($pattern);

        $q = 'MATCH (n) RETURN n';
        $result = $this->sendQuery($q);

        $this->assertEquals(10, $result->getNodesCount());
        $this->assertEquals('Person', $result->getSingleNode()->getLabel());
        $this->assertInternalType('integer', $result->getSingleNode()->getProperty('age'));
        $this->assertTrue($result->getSingleNode()->getProperty('age') >= 18 && $result->getSingleNode()->getProperty('age') <= 50);
        $this->assertTrue($result->getSingleNode()->hasProperty('firstname'));
    }

    public function testPatternWithEdges()
    {
        $pattern = '(p:Person *15)-[:WORKS_AT *n..1]->(c:Company {name: company} *7)';
        $this->loadGraphInDB($pattern);

        $q = 'MATCH p=(n:Person)-[:WORKS_AT]->(c:Company) RETURN p';
        $result = $this->sendQuery($q);

        $this->assertCount(15, $result->getNodesByLabel('Person'));
        $this->assertCount(1, $result->getSingleNode('Person')->getRelationships('WORKS_AT'));

        $q = 'MATCH (n:Company) RETURN n';
        $result = $this->sendQuery($q);
        $this->assertCount(7, $result->getNodesByLabel('Company'));
    }

    public function testPatternWithEdgesAndProps()
    {
        $pattern = '(p:Person *15)-[:WORKS_AT {level: randomDigit} *n..1]->(c:Company {name: company} *7)';
        $this->loadGraphInDB($pattern);

        $q = 'MATCH p=(n:Person)-[:WORKS_AT]->(c:Company) RETURN p';
        $result = $this->sendQuery($q);

        $this->assertCount(15, $result->getNodesByLabel('Person'));
        $this->assertCount(1, $result->getSingleNode('Person')->getRelationships('WORKS_AT'));
        $this->assertTrue($result->getSingleNode()->getSingleRelationship('WORKS_AT')->hasProperty('level'));

        $q = 'MATCH (n:Company) RETURN n';
        $result = $this->sendQuery($q);
        $this->assertCount(7, $result->getNodesByLabel('Company'));
    }

    public function testMultiplePatternsOnOneLine()
    {
        $pattern = '(p:Person *15)-[:WORKS_AT {level: randomDigit} *n..1]->(c:Company {name: company} *7)-[:IN_MARKET *n..1]->(m:Market *5)';
        $this->loadGraphInDB($pattern);

        $q = 'MATCH p=(person:Person)-[*]->(m:Market) RETURN p';
        $result = $this->sendQuery($q);

        $person = $result->getSingleNode('Person');
        $this->assertTrue('Person' === $person->getLabel());
        $this->assertEquals('WORKS_AT', $person->getSingleRelationship()->getType());
        $this->assertEquals('Company', $person->getSingleRelationship()->getEndNode()->getLabel());
        $this->assertEquals('Market', $person->getSingleRelationship()->getEndNode()
            ->getSingleRelationship('IN_MARKET')->getEndNode()->getLabel());
    }

    public function testSiteExamplePattern()
    {
        $pattern = '(p:Person {firstname: firstName, lastname: lastName} *10)-[:KNOWS *n..n]->(p)
(p)-[:HAS *n..n]->(s:Skill {name: progLanguage} *10)
(c:Company {name: company, desc: catchPhrase} *5)-[:LOOKS_FOR_COMPETENCE *n..n]->(s)
(c)-[:LOCATED_IN *n..1]->(country:Country {name: country} *5)
(p)-[:LIVES_IN *n..1]->(country)';

        $this->loadGraphInDB($pattern);
        $q = 'MATCH p=(n)-[*]-() RETURN p LIMIT 10';
        $result = $this->sendQuery($q);

        $q = 'MATCH (n:Skill) RETURN n';
        $result = $this->sendQuery($q);

        $this->assertCount(10, $result->getNodes());

        $q = 'MATCH p=(person:Person)-[:HAS]->() RETURN p';
        $result = $this->sendQuery($q);

        $this->assertTrue($result->getSingleNode()->hasRelationships());
;    }

    public function test1NCardinality()
    {
        $p = '(g:Genre *6)-[:HAS_CHANNEL *1..n]->(channel:Channel *8)';
        $this->loadGraphInDB($p);
        $query = 'MATCH p=(g:Genre)-[:HAS_CHANNEL]->(channel:Channel) RETURN p';
        $result = $this->sendQuery($query);

        $this->assertCount(6, $result->getNodesByLabel('Genre'));
        $this->assertCount(8, $result->getNodesByLabel('Channel'));
        $this->assertCount(8, $result->getRelationships());
        $this->assertEquals('HAS_CHANNEL', $result->getSingleNode()->getSingleRelationship()->getType());

        $this->clearDB();
        $p = '(g:Genre *6)-[:HAS_CHANNEL *1..n]->(channel:Channel *37)';
        $this->loadGraphInDB($p);
        $query = 'MATCH p=(g:Genre)-[:HAS_CHANNEL]->(channel:Channel) RETURN p';
        $result = $this->sendQuery($query);

        $this->assertCount(6, $result->getNodesByLabel('Genre'));
        $this->assertCount(37, $result->getNodesByLabel('Channel'));
        $this->assertCount(37, $result->getRelationships());
        $this->assertEquals('HAS_CHANNEL', $result->getSingleNode()->getSingleRelationship()->getType());

        $this->clearDB();
        $p = '(g:Genre *35)-[:HAS_CHANNEL *1..n]->(channel:Channel *8)';
        $this->loadGraphInDB($p);
        $query = 'MATCH p=(g:Genre)-[:HAS_CHANNEL]->(channel:Channel) RETURN p';
        $result = $this->sendQuery($query);
        $this->assertCount(8, $result->getNodesByLabel('Channel'));
        $this->assertCount(8, $result->getRelationships());
        $this->clearDB();
    }

    public function testLinkedList()
    {
        $p = '(root:Root *1)-[:LINK *1..n]->(link1:Link *1)-[:LINK *1..n]->(link2:Link2 *1)-[:LINK *1..n]->(root)';
        $this->clearDB();
        $this->loadGraphInDB($p);

        $q = 'MATCH p=(n)-[r]-() RETURN p';
        $result = $this->sendQuery($q);

        $root = $result->getSingleNodeByLabel('Root');
        $this->assertTrue($root->getSingleRelationship('LINK', 'OUT')->getEndNode()->getLabel() == 'Link');
        //$link = $result->getSingleNodeByLabel('Link');
        //$this->assertTrue($link->getSingleRelationship('LINK', 'OUT')->getEndNode()->getLabel()->getSingleRelationship('LINK', 'OUT')->getEndNode()->getLabel() == 'Root');
    }

    public function testEdgeWithMultiProps()
    {
        $p = '(person:Person {name: firstName, lastname:{numberBetween:[2,5]}} *15)-[:WORKS_AT {weight:{numberBetween:[2,8]}, activity:{numberBetween:[2,50]}} *n..1]->(company:Company {name:company}*10)';
        $this->clearDB();
        $this->loadGraphInDB($p);

        $q = 'MATCH p=(:Person)-[:WORKS_AT]->(:Company) RETURN p';
        $result = $this->sendQuery($q);

        $this->assertCount(15, $result->getRelationships());

    }

    public function testOneToOneCardinality()
    {
        $p = '(root:Root)-[:CHILD *1..1]->(child:Child)';
        $this->clearDB();
        $this->loadGraphInDB($p);

        $q = 'MATCH p=(root:Root)-[r]-() RETURN p';
        $result = $this->sendQuery($q);

        $this->assertTrue($result->getSingleNode('Root')->getLabel() == 'Root');
        $this->assertTrue($result->getSingleNode('Root')->getSingleRelationship()->getType() == 'CHILD');
        $this->assertTrue($result->getSingleNode('Root')->getSingleRelationship()->getEndNode()->getLabel() == 'Child');

    }

    public function testOneToOneCardinality2()
    {
        $p = '(root:Root2 *10)-[:CHILD2 *1..1]->(child:Child *7)';
        $this->clearDB();
        $this->loadGraphInDB($p);

        $q = 'MATCH p=(root:Root2)-[r:CHILD2]-() RETURN p';
        $result = $this->sendQuery($q);

        $this->assertTrue($result->getSingleNode('Root2')->getLabel() == 'Root2');
        $this->assertTrue($result->getSingleNode('Root2')->getSingleRelationship()->getType() == 'CHILD2');
        $this->assertTrue($result->getSingleNode('Root2')->getSingleRelationship()->getEndNode()->getLabel() == 'Child');
        $this->assertEquals(7, $result->getRelationshipsCount());

    }

    public function testOneToOneCardinality3()
    {
        $p = '(root:Root3 *5)-[:CHILD3 *1..1]->(child:Child *7)';
        $this->clearDB();
        $this->loadGraphInDB($p);

        $q = 'MATCH p=(root:Root3)-[r:CHILD3]-() RETURN p';
        $result = $this->sendQuery($q);

        $this->assertTrue($result->getSingleNode('Root3')->getLabel() == 'Root3');
        $this->assertTrue($result->getSingleNode('Root3')->getSingleRelationship()->getType() == 'CHILD3');
        $this->assertTrue($result->getSingleNode('Root3')->getSingleRelationship()->getEndNode()->getLabel() == 'Child');
        $this->assertEquals(5, $result->getRelationshipsCount());

    }

    public function getClient()
    {
        if (null === $this->client) {
            $client = new Client();
            $client->addConnection('default', 'http', 'localhost', 7474)
                ->build();

            $this->client = $client;
        }

        return $this->client;
    }

    public function sendQuery($q, array $p = array())
    {
        $response = $this->getClient()->sendCypherQuery($q, $p, null, array('row', 'graph'));
        sleep(0.2);
        $formatter = new ResponseFormatter();
        $result = $formatter->format($response);

        return $result;
    }

    public function loadGraphInDB($pattern)
    {
        $this->clearDB();
        $gen = new Neogen();
        $schema = $gen->generateGraphFromCypher($pattern);

        $converter = new CypherStatementsConverter();
        $converter->convert($schema);

        $statements = $converter->getStatements();
        foreach ($statements as $statement){
            if (is_array($statement)){
                foreach($statement as $st){
                    $props = isset($st['parameters']) ? $st['parameters'] : array();
                    $this->sendQuery($st['statement'], $props);
                }
            } else {
                $props = isset($statement['parameters']) ? $statement['parameters'] : array();
                $this->sendQuery($statement['statement'], $props);
            }
        }
    }

    public function clearDB()
    {
        $q = 'MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n';
        $this->sendQuery($q);
    }
}