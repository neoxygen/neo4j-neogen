<?php

namespace Neoxygen\Neogen\Converter;

use Neoxygen\Neogen\Graph\Graph;

interface ConverterInterface
{
    public function convert(Graph $graph);
}
