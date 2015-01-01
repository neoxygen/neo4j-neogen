<?php

namespace Neoxygen\Neogen\Parser;

interface ParserInterface
{
    public function parse($definition);

    public function getSchema();

    public function getName();
}