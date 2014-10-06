<?php

namespace Neoxygen\Neogen\Tests\Parser;

use Neoxygen\Neogen\Parser\CypherPattern;

class CypherPatternTest extends \PHPUnit_Framework_TestCase
{
    public function testNodePattern()
    {
        $pattern = '(p:Person )';
        $parser = new CypherPattern();
        $parser->parseCypher($pattern);
    }
}