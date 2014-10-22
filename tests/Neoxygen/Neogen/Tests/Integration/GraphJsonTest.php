<?php

namespace Neoxygen\Neogen\Tests\Integration;

use Neoxygen\Neogen\Neogen,
    Neoxygen\Neogen\Converter\GraphJSONConverter;

class GraphJsonTest extends \PHPUnit_Framework_TestCase
{
    public function testGraphToJson()
    {
        $gen = new Neogen();
        $p = '// Example :
(p:Person:#User {name: fullName} *35)-[:KNOWS *n..n]->(p)
(p)-[:HAS *n..n]->(s:Skill {name: progLanguage} *20)
(c:Company *20)-[:LOOKS_FOR_COMPETENCE *n..n]->(s)
(c)-[:LOCATED_IN *n..1]->(country:Country {name: country} *70)
(p)-[:LIVES_IN *n..1]->(country)';
        $graph = $gen->generateGraphFromCypher($p);
        $converter = new GraphJSONConverter();
        $json = $converter->convert($graph);
        //print_r($json);
    }

    public function testSimpleNode()
    {
        $gen = new Neogen();
        $p = '(p:Person:User *3)';
        $graph = $gen->generateGraphFromCypher($p);
        print_r($graph);
    }

    public function testLinkedList()
    {
        $gen = new Neogen();
        $p = '(test:Test *10)
        (test)-[:NEXT *1..1]->(test)';
        $graph = $gen->generateGraphFromCypher($p);
        print_r($graph);
    }


}