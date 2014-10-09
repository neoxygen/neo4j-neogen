<?php

require_once __DIR__.'/vendor/autoload.php';

use Neoxygen\Neogen\Neogen;

$text = '(p:Person {firstname: firstName} *500)-[:WRITE { created_at: dateTime } *1..n]->(post:Post *500)
(p)-[:KNOWS {since: { dateTimeBetween: ["-10 years", "-5 years"] }} *n..n]->(p)
(p)-[:COMMENTED_ON *n..n]->(post)
(p)-[:HAS *n..n]->(s:Skill *100)
(c:Company *50)-[:LOOKS_FOR *n..n]->(s)';

$gen = new Neogen();

$start = microtime(true);
$graph = $gen->generateGraphFromCypher($text);
$diff = microtime(true) - $start;
echo $graph->getNodesCount() .' nodes & '. $graph->getEdgesCount() . ' edges generated in '.$diff.' seconds';