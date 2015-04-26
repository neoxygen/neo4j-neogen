<?php

namespace Neoxygen\Neogen\Parser;

interface ParserInterface
{
    public function parse($definition);

    public function getName();
}
