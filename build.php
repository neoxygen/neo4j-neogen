<?php

require_once(__DIR__.'/vendor/autoload.php');

use Neoxygen\Neogen\Builder;

$neogen = Builder::create()
    ->build();

print_r($neogen);