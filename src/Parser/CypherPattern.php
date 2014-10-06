<?php

namespace Neoxygen\Neogen\Parser;

use Neoxygen\Neogen\Exception\CypherPatternException;

class CypherPattern
{
    const NODE_PATTERN = '/((\\()([\\w\\d]+)?(:)([\\w\\d]+\\s*)(\\)))/';

    const EDGE_PATTERN = '/((<?>?-\[)([:_\w\d]+)(\s?{(.*)})?(\s[\w\d]\.\.[\w\d])(\]-<?>?))/';

    const INGOING_RELATIONSHIP = 'IN';

    const OUTGOING_RELATIONSHIP = 'OUT';

    const DEFAULT_RELATIONSHIP_DIRECTION = 'OUT';

    private $nodes = [];

    private $edges = [];

    private $identifiers = [];

    public function parseCypher($cypherPattern)
    {
        $lines = $this->splitLineBreaks($cypherPattern);

        foreach ($lines as $line) {
            $parts = $this->parseLine($line);
            foreach($parts as $key => $part){
                $this->parsePart($part);
            }
        }
    }

    public function splitLineBreaks($cypherPattern)
    {
        $lines = explode("\n", $cypherPattern);

        return $lines;
    }

    public function parseLine($cypherLineText)
    {
        $parts = preg_split(self::NODE_PATTERN, $cypherLineText, null, PREG_SPLIT_NO_EMPTY);

        return $parts;
    }

    public function parsePart($partText)
    {
        echo preg_match(self::NODE_PATTERN, $partText);
        if (preg_match(self::NODE_PATTERN, $partText, $output)) {
            $this->processNodePart($output);
        } elseif (preg_match(self::EDGE_PATTERN, $partText)) {
            exit();
            return 'EDGE';
        } else {
            throw new CypherPatternException(sprintf('The part "%s" could not be parsed, check it for type errors.', $partText));
        }
    }

    public function processNodePart(array $pregMatchOutput)
    {
        print_r($pregMatchOutput);
    }
}