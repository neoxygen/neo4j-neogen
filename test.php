<?php

require_once __DIR__.'/vendor/autoload.php';

use Neoxygen\Neogen\Neogen;
use Neoxygen\Neogen\Schema\GraphSchemaBuilder;

$neogen = Neogen::create()
    ->build();

$gsb = new GraphSchemaBuilder();
$file = __DIR__.'/neogen.yml';
$p = $neogen->getParserManager()->getParser('YamlParser');
$userSchema = $p->parse($file);

$def = $gsb->buildGraph($userSchema);
$gen = $neogen->getGraphGenerator();
$gen->generateFromFile($def);

print_r($def);