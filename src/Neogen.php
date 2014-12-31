<?php

namespace Neoxygen\Neogen;

use Neoxygen\Neogen\Parser\CypherPattern,
    Neoxygen\Neogen\Parser\YamlFile as Parser,
    Neoxygen\Neogen\Generator\GraphGenerator;

class Neogen
{
    private static $version = '0.1-dev';

    private $generator;

    public function __construct($seed = null)
    {
        $this->generator = new GraphGenerator($seed);
    }

    public static function getVersion()
    {
        return self::$version;
    }

    public function generateGraphFromFile($file)
    {
        $parser = new Parser();
        $schema = $parser->parseSchema($file);

        return $this->generator->generate($schema);
    }

    public function generateGraphFromCypher($cypher, $precalculationOnly = false)
    {
        $parser = new CypherPattern();
        $parser->parseCypher($cypher);

        return $this->generator->generate($parser->getSchema(), $precalculationOnly);
    }
}
