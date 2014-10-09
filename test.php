<?php

require_once __DIR__.'/vendor/autoload.php';

use Neoxygen\Neogen\Neogen,
    Neoxygen\Neogen\Converter\GraphJSONConverter,
    Neoxygen\Neogen\Converter\CypherStatementsConverter,
    Neoxygen\NeoClient\Client;


$text = '(p:Person {firstname: firstName, lastname: lastName } *50)-[:WRITE { created_at: dateTime } *1..n]->(post:Post *50)
(p)-[:KNOWS {since: { dateTimeBetween: ["-10 years", "-5 years"] }} *n..n]->(p)
(p)-[:COMMENTED_ON *n..n]->(post)
(p)-[:HAS *n..n]->(s:Skill *100)
(c:Company *50)-[:LOOKS_FOR {nom: firstName} *n..n]->(s)';

$gen = new Neogen();

$start = microtime(true);
$graph = $gen->generateGraphFromCypher($text);


$converter = new GraphJSONConverter();
$json = $converter->convert($graph);

$cypher = new CypherStatementsConverter();
$cypher->convert($graph);

$client = new Client();
$client->addConnection('default', 'http', 'localhost', 7474)
    ->build();

$tx = $client->openTransaction();
$decode = json_decode($tx, true);
$commit = $decode['commit'];
$p = '/(?:\\/)(\\d+)(?:\\/commit)/';
preg_match($p, $commit, $output);
$txid = $output[1];
print_r($txid);
foreach ($cypher->getNodeStatements() as $node){
    $response = $client->pushToTransaction($txid, $node['statement'], $node['parameters']);
    print($response);
}
foreach ($cypher->getEdgeStatements() as $edgeType) {

    foreach ($edgeType as $edge) {
        $props = !isset($edge['param']) ? array() : $edge['param'];
        $response = $client->pushToTransaction($txid, $edge['statement'], $props);
        print($response);
    }
}
$response = $client->commitTransaction($txid);
print($response);
$diff = microtime(true) - $start;
echo $graph->getNodesCount() .' nodes & '. $graph->getEdgesCount() . ' edges generated in '.$diff.' seconds'."\n";

