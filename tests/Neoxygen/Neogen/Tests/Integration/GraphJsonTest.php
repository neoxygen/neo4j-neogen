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
(p:Person {firstname: firstName, lastname: lastName } *35)-[:KNOWS *n..n]->(p)
(p)-[:HAS *n..n]->(s:Skill {name: progLanguage} *20)
(c:Company {name: company, desc: catchPhrase} *20)-[:LOOKS_FOR_COMPETENCE *n..n]->(s)
(c)-[:LOCATED_IN *n..1]->(country:Country {name: country} *70)
(p)-[:LIVES_IN *n..1]->(country)';
        $graph = $gen->generateGraphFromCypher($p);
        $converter = new GraphJSONConverter();
        $json = $converter->convert($graph);
        print_r($json);
    }
}