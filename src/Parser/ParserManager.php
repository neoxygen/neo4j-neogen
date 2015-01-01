<?php

namespace Neoxygen\Neogen\Parser;

use Neoxygen\Neogen\Parser\ParserInterface;
use Neoxygen\Neogen\Exception\ParserNotFoundException;

class ParserManager
{
    /**
     * @var array
     */
    protected $parsers = [];

    /**
     * Returns the registered schema definition parsers
     *
     * @return array
     */
    public function getParsers()
    {
        return $this->parsers;
    }

    /**
     * Register a new schema definition parser
     *
     * @param \Neoxygen\Neogen\Parser\ParserInterface $parser
     */
    public function registerParser(ParserInterface $parser)
    {
        $this->parsers[$parser->getName()] = $parser;
    }

    /**
     * Checks whether or not at least one parser exist
     *
     * @return bool
     */
    public function hasParsers()
    {
        if (!empty($this->parsers)) {

            return true;
        }

        return false;
    }

    /**
     * Checks whether or not the parser with the <code>name</code> value exist
     *
     * @param $name
     * @return bool
     */
    public function hasParser($name)
    {
        if (array_key_exists($name, $this->parsers)) {

            return true;
        }

        return false;
    }

    /**
     * Returns the schema definition parser for the <code>name</code> value
     *
     * @param $name
     * @return \Neoxygen\Neogen\Parser\ParserInterface
     * @throws ParserNotFoundException
     */
    public function getParser($name)
    {
        if (!$this->hasParser($name)) {
            throw new ParserNotFoundException(sprintf('The parser with NAME "%s" is not registered', $name));
        }
        return $this->parsers[$name];
    }
}
