<?php

namespace Neoxygen\Neogen\Parser;

use Neoxygen\Neogen\Exception\CypherPatternException;
use Neoxygen\Neogen\Exception\SchemaException,
    Neoxygen\Neogen\Schema\GraphSchemaDefinition;
use Symfony\Component\Yaml\Yaml,
    Symfony\Component\Yaml\Exception\ParseException;

class CypherPattern
{
    const NODE_PATTERN = '/(^(\\()([_\w\d]+)([:\w\d]+)*(\s?{[,:~\'\"{}\[\]\s\w\d]+})?(\s?\*\d+)?(\s*\))$)/';

    const EDGE_PATTERN = '/(<?>?-\[)(?::)([_\w\d]+)(\s?{(?:.*)})?(\s?\*[\w\d+]\.\.[\w\d])(\]-<?>?)/';

    const SPLIT_PATTERN = "/((?:<?->?)(?:\\[[^<^>.]*\\*[a-z0-9]+\\.\\.[a-z0-9]+\\])(?:<?->?))/";

    const INGOING_RELATIONSHIP = 'IN';

    const OUTGOING_RELATIONSHIP = 'OUT';

    private $nodes;

    private $edges;

    private $labels;

    private $identifiers;

    private $nodeInfoMap;

    public function parseCypher($cypherPattern)
    {
        $this->nodes = [];
        $this->edges = [];
        $this->labels = [];
        $this->identifiers = [];
        $this->nodeInfoMap = [];
        $lines = explode("\n", $this->preFormatPattern($cypherPattern));

        foreach ($lines as $line) {
            $parts = $this->parseLine($line);
            foreach($parts as $key => $part){
                if (preg_match(self::NODE_PATTERN, $part, $output)) {
                    $nodeInfo = $this->getNodePatternInfo($output, $part);
                    $this->processNode($nodeInfo, $part);
                } elseif (preg_match(self::EDGE_PATTERN, $part, $output)) {
                    $edgeInfo = $this->getEdgePatternInfo($output);
                    $this->processEdge($edgeInfo, $key, $parts);
                } else {
                    throw new CypherPatternException(sprintf('The part "%s" could not be parsed, check it for type errors.', $part));
                }
            }
        }
    }

    public function preFormatPattern($pattern)
    {
        $lines = explode("\n", $pattern);
        $paste = '';
        foreach ($lines as $line){
            $l = trim($line);
            if (false === strpos($l, '//')) {
                $paste .= $l;
            }
        }
        $formatInLines = str_replace(')(',")\n(", $paste);

        return $formatInLines;
    }

    public function splitLineBreaks($cypherPattern)
    {
        $lines = explode("\n", $cypherPattern);
        $parsedLines = [];
        foreach ($lines as $line) {
            if (false === strpos($line, '//')) {
                $parsedLines[] = trim(htmlspecialchars_decode($line));
            }
        }

        return $parsedLines;
    }

    public function parseLine($cypherLineText)
    {
        $parts = preg_split(self::SPLIT_PATTERN, $cypherLineText, null, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

        return $parts;
    }

    public function processNode(array $nodeInfo, $part = null)
    {
        $identifier = $nodeInfo['identifier'];
        if (array_key_exists($identifier, $this->nodes)){
            return;
        }
        if (empty($nodeInfo['labels']) && !array_key_exists($nodeInfo['identifier'], $this->nodes)){
            throw new SchemaException(sprintf('The identifier "%s" has not been declared in "%s"', $nodeInfo['identifier'], $part));
        }

        $labels = $nodeInfo['labels'];

        $node = [
            'identifier' => $identifier,
            'labels' => $labels,
            'count' => $nodeInfo['count'],
            'properties' => $nodeInfo['properties']
        ];
        if (null !== $nodeInfo['identifier']) {
            $this->identifiers[$nodeInfo['identifier']] = $identifier;
            if (null !== $identifier) {
                $virtualPart = '('.$nodeInfo['identifier'].')';
                $this->nodeInfoMap[$virtualPart] = $nodeInfo;
            }

            if (null !== $part && null !== $nodeInfo['identifier']) {
                $this->nodeInfoMap[trim($part)] = $nodeInfo;
            }
        }

        if ($nodeInfo['properties']){

            try {
                $properties = Yaml::parse($node['properties']);
                if (null !== $properties) {
                    foreach ($properties as $key => $type) {
                        if (is_array($type)){
                            $props[$key]['type'] = key($type);
                            $props[$key]['params'] = [];
                            foreach(current($type) as $k => $v) {
                                $props[$key]['params'][] = $v;
                            }
                        } else {
                            $props[$key] = $type;
                        }
                    }
                    $node['properties'] = $props;
                }

            } catch (ParseException $e){
                throw new CypherPatternException(sprintf('Malformed inline properties near "%s"', $node['properties']));
            }
        }

        $this->nodes[$identifier] = $node;
        $this->labels[] = $labels;
    }

    public function processEdge(array $edgeInfo, $key, array $parts)
    {
        $prev = trim($parts[$key-1]);
        $previous = $this->nodeInfoMap[trim($prev)];
        $prevNode = $previous['identifier'];

        $next = trim($parts[$key+1]);

        preg_match(self::NODE_PATTERN, $next, $output);
        $info = $this->getNodePatternInfo($output, $next);
        $this->processNode($info);

        $nextious = $this->nodeInfoMap[trim($next)];
        $nextNode = !empty($nextious['identifier']) ? $nextious['identifier'] : $this->identifiers[$nextious['identifier']];

        $start = 'OUT' === $edgeInfo['direction'] ? $prevNode : $nextNode;
        $end = 'OUT' === $edgeInfo['direction'] ? $nextNode : $prevNode;

        $edge = [
            'start' => $start,
            'end' => $end,
            'type' => $edgeInfo['type'],
            'mode' => $edgeInfo['cardinality'],
        ];

        try {
            $props = [];
            $properties = Yaml::parse($edgeInfo['properties']);
            if (null !== $properties) {
                foreach ($properties as $key => $type) {
                    if (is_array($type)){
                        $props[$key]['type'] = key($type);
                        $props[$key]['params'] = [];
                        foreach(current($type) as $k => $v) {
                            $props[$key]['params'][] = $v;
                        }
                    } else {
                        $props[$key] = $type;
                    }
                }
            }
            $edge['properties'] = $props;
        } catch (ParseException $e) {
            throw new CypherPatternException(sprintf('Malformed inline properties near "%s"', $edgeInfo['properties']));
        }

        $this->edges[] = $edge;

    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getEdges()
    {
        return $this->edges;
    }

    public function getIdentifiers()
    {
        return $this->identifiers;
    }

    public function hasIdentifier($identifier)
    {
        return array_key_exists($identifier, $this->identifiers);
    }

    public function getNodePatternInfo(array $nodePattern, $part)
    {
        if (empty($nodePattern[3])){
            throw new SchemaException(sprintf('An identifier must be defined for nodes in "%s"', $part));
        }

        $labels = explode(':', trim($nodePattern['4']));
        array_shift($labels);
        $defaultInfo = [
            'identifier' => $this->nullString($nodePattern[3]),
            'labels' => $labels,
            'properties' => $this->nullString($nodePattern[5]),
            'count' => $this->nullString($nodePattern[6])
        ];
        if (empty($defaultInfo['count']) || '' == $defaultInfo['count']){
            $defaultInfo['count'] = 1;
        }

        $this->nodeInfoMap[trim($part)] = $defaultInfo;

        return $defaultInfo;
    }

    public function getEdgePatternInfo(array $edgePattern)
    {
        $arrowStart = $edgePattern[1];
        $arrowEnd = $edgePattern[5];
        $direction = $this->detectEdgeDirection($arrowStart, $arrowEnd);
        if (null === $direction) {
            $edgePart = &$edgePattern;
            unset($edgePart[0]);
            $patt = implode('', $edgePart);
            throw new SchemaException(sprintf('The direction of the relationship must be defined, near "%s".', $patt));
        }
        $type = $this->nullString($edgePattern[2]);
        if (null === $type) {
            throw new SchemaException(sprintf('The type of the relationship must be defined, near "%s"', $edgePattern));
        }
        $edge = [
            'type' => $type,
            'direction' => $direction,
            'cardinality' => $this->checkCardinality($edgePattern),
            'properties' => $this->nullString($edgePattern[3])
        ];

        return $edge;

    }

    public function getNodePattern()
    {
        return self::NODE_PATTERN;
    }

    public function getEdgePattern()
    {
        return self::EDGE_PATTERN;
    }

    public function checkCardinality($pattern)
    {
        $allowedCardinalities = ['1..n', 'n..n', '1..1', 'n..1'];
        $cardinality = str_replace('*', '', trim($pattern[4]));
        if (!in_array($cardinality, $allowedCardinalities)) {
            throw new SchemaException(sprintf('The cardinality "%s" is not allowed', $cardinality));
        }

        return $cardinality;
    }

    public function getSchema()
    {
        $schema = new GraphSchemaDefinition();
        $schema->setNodes($this->nodes);
        $schema->setEdges($this->edges);

        return $schema;
    }

    private function detectEdgeDirection($start, $end)
    {
        if ($start === '-[' && $end === ']->') {
            return self::OUTGOING_RELATIONSHIP;
        } elseif ($start === '<-[' && $end === ']-'){
            return self::INGOING_RELATIONSHIP;
        }

        return null;
    }

    private function nullString($string)
    {
        if (trim($string) === '') {
            return null;
        }

        return str_replace('*', '', trim($string));
    }
}