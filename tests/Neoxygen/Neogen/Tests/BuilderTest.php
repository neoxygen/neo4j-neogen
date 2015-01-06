<?php

namespace Neoxygen\Neogen\Tests;

use Neoxygen\Neogen\Neogen;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $neogen;

    public function setup()
    {
        $neogen = Neogen::create()
            ->build();

        $this->neogen = $neogen;
    }

    public function testParserManagerIsRegistered()
    {
        $this->assertNotEmpty($this->neogen->getParserManager());
    }
}

