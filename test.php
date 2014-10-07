<?php

require_once __DIR__.'/vendor/autoload.php';

use Neoxygen\Neogen\Parser\CypherPattern,
    Neoxygen\Neogen\Schema\Processor;
use Neoxygen\NeoClient\Client;
use Faker\Factory;
use Symfony\Component\Yaml\Dumper;


$start = microtime(true);
$parser = new CypherPattern();
$faker = Factory::create();
$processor = new Processor();

$models = [
    'PERSON' => [
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'dateOfBirth' => ['type' => 'dateTimeBetween', 'params' => ['-65 years', '-18 years']]
    ],
    'POST' => [
        'title' => ['type' => 'sentence', 'params' => [8]],
        'text' => 'realText'
    ],
    'TEENAGER' => [
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'dateOfBirth' => ['type' => 'dateTimeBetween', 'params' => ['-17 years', '-12 years']]
    ],
    'KID' => [
        'firstname' => 'firstName',
        'lastname' => 'lastName',
        'dateOfBirth' => ['type' => 'dateTimeBetween', 'params' => ['-12 years', '-3 years']]
    ],
    'COMPANY' => [
        'name' => 'company',
        'description' => 'catchPhrase'
    ]
];

$text = '(p:Person 20)-[:WRITE 1..n]->(post:Post 35)
(p)-[:KNOWS n..n]->(p)
(p)-[:COMMENTED_ON n..n]->(post)';


$parser->parseCypher($text);
echo '-----';
$lap = microtime(true);
$lapdiff = $lap - $start;
echo $lapdiff."\n";

$cypherSchema = $parser->getSchema();
$schema = [
    'nodes' => [],
    'relationships' => []
];
foreach ($cypherSchema['nodes'] as $node) {
    $model = strtoupper($node['label']);
    if (array_key_exists($model, $models)) {
        $node['properties'] = $models[$model];
    }
    $schema['nodes'][] = $node;
}

foreach ($cypherSchema['relationships'] as $edge){
    $schema['relationships'][uniqid().strtolower($edge['type'])] = $edge;
}

$processor->process($schema);

$json = $processor->getGraphJson();

echo $json;
exit();

$end = microtime(true);
$diff = $end - $start;
echo $diff;

$client = new Client();
$client->addConnection('default', 'http', 'localhost', 7474);
$client->build();
//$formatter = new ResponseFormatter();

$constraints = $processor->getConstraints();
$queries = $processor->getQueries();

foreach ($constraints as $constraint) {
    $client->sendCypherQuery($constraint);
}

$max = 50;
$i = 1;
$q = '';
foreach ($queries as $query) {
    $q .= $query."\n";
    if ($i >= $max) {
        $i = 0;
        $response = $client->sendCypherQuery($q);
        print_r($response);
        $q = '';
    }
    $i++;
}
if ($q !== '') {
    $response = $client->sendCypherQuery($q);
    print_r($response);
}


$end = microtime(true);
$diff = $end - $start;
echo $diff;

