<?php

require_once __DIR__.'/vendor/autoload.php';

use Neoxygen\Neogen\Neogen;
use Neoxygen\Neogen\Schema\GraphSchemaBuilder;

//gc_disable();

$neogen = Neogen::create()
    ->build();

$gsb = new GraphSchemaBuilder();
$file = file_get_contents(__DIR__.'/neogen.cypher');
$p = $neogen->getParserManager()->getParser('cypher');
$userSchema = $p->parse($file);

$def = $gsb->buildGraph($userSchema);
$gen = $neogen->getGraphGenerator();
$g = $gen->generateGraph($def);

print_r($g);
