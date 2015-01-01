<?php

namespace Neoxygen\Neogen\Tests;

use Neoxygen\Neogen\NeogenBuilder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $neogen;

    public function setup()
    {
        $neogen = NeogenBuilder::create()
            ->build();

        $this->neogen = $neogen;
    }

    public function testParserManagerIsRegistered()
    {
        $this->assertNotEmpty($this->neogen->getParserManager());
    }
}

