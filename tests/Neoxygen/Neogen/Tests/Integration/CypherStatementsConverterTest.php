<?php

namespace Neoxygen\Neogen\Tests\Integration;

use Neoxygen\Neogen\Converter\CypherStatementsConverter,
    Neoxygen\Neogen\Neogen,
    Neoxygen\NeoClient\ClientBuilder;

class CypherStatementsConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConversion()
    {
        $gen = new Neogen();
        $p = '(person:Person {firstname:firstName, lastname:lastName} *5)-[:WORKS_AT *n..1]->(company:Company {name:company} *10)
        (actor:Person:Actor {name:fullName, birthdate:{dateTimeBetween:["-50 years","-18 years"]}} *10)-[:WORKS_AT {since:{dateTimeBetween:["-5 years","-1 years"]}} *n..1]->(company)';
        $graph = $gen->generateGraphFromCypher($p);

        $converter = new CypherStatementsConverter();
        $converter->convert($graph);

        $client = ClientBuilder::create()
            ->addDefaultLocalConnection()
            ->build();

        $client->sendCypherQuery('MATCH (n) OPTIONAL MATCH (n)-[r]-() DELETE r,n');

        $statements = $converter->getStatements();
        foreach ($statements['constraints'] as $st){
            $client->sendCypherQuery($st['statement']);
        }
        foreach ($statements['nodes'] as $st){
            $client->sendCypherQuery($st['statement'], $st['parameters']);
        }
        foreach ($statements['edges'] as $st){
            if (!isset($st['parameters'])){
                $parameters = array();
            } else {
                $parameters = $st['parameters'];
            }
            $client->sendCypherQuery($st['statement'], $parameters);
        }

    }
}