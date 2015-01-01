<?php

namespace Neoxygen\Neogen\Tests\Integration;

use Neoxygen\Neogen\Neogen,
    Neoxygen\Neogen\Converter\StandardCypherConverter;

class StandardCypherConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testGraphToCypherStatements()
    {
        $gen = new Neogen();
        $p = '(person:Person {firstname:firstName, lastname:lastName} *5)-[:WORKS_AT *n..1]->(company:Company {name:company} *10)
        (actor:Person:Actor {name:fullName, birthdate:{dateTimeBetween:["-50 years","-18 years"]}} *10)-[:WORKS_AT {since:{dateTimeBetween:["-5 years","-1 years"]}} *n..1]->(company)';
        $graph = $gen->generateGraphFromCypher($p);
        $converter = new StandardCypherConverter();
        $converter->convert($graph);

        $file = getcwd().'/sts.cql';
        $contents = '';
        foreach($converter->getStatements() as $st){
            $contents .= $st . "\n";
        }
        file_put_contents($file, $contents);

    }
}