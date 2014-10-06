<?php

require_once __DIR__.'/vendor/autoload.php';

use Neoxygen\Neogen\Schema\PatternParser,
    Neoxygen\Neogen\Schema\Processor;
use Neoxygen\NeoClient\Client;
use Faker\Factory;
use Symfony\Component\Yaml\Dumper;


$start = microtime(true);
$parser = new PatternParser();
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

$text = '(:Person *20*)-[:WRITE *1..n*]->(:Post *35*)
(:Person)-[:KNOWS *n..n*]->(:Person)
(:Person)-[:COMMENTED_ON *n..n*]->(:Post)';

$text = null;























$parser->parse($text);
echo '-----';
exit();
$lap = microtime(true);
$lapdiff = $lap - $start;
echo $lapdiff."\n";

$schema = $parser->getSchema();
foreach ($schema['nodes'] as $type => $node) {
    $model = strtoupper($type);
    if (array_key_exists($model, $models)) {
        $schema['nodes'][$type]['properties'] = $models[$model];
    }
}

$processor->process($schema);

$json = $processor->getGraphJson();

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

