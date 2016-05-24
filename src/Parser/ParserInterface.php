<?php

namespace GraphAware\Neogen\Parser;

interface ParserInterface
{
    public function parse($definition);

    public function getName();
}
