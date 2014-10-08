<?php

namespace Neoxygen\Neogen\Parser;

use Neoxygen\Neogen\Exception\CypherPatternException;
use Neoxygen\Neogen\Exception\SchemaException;
use Symfony\Component\Yaml\Yaml,
    Symfony\Component\Yaml\Exception\ParseException;

class CypherPattern
{
    const NODE_PATTERN = '/((\\()([\\w\\d]+)?(:?([\\w\\d]+))?(\\s?{[,:~\\\'\\"{}\\[\\]\\s\\w\\d]+})?(\\s?\\d+)?(\\s*\\)))/';

    const EDGE_PATTERN = '/(<?>?-\[)(?::)([_\w\d]+)(\s?{(?:.*)})?(\s[\w\d]\.\.[\w\d])(\]-<?>?)/';

    const SPLIT_PATTERN = '/((?:<?>?-).*\\s?(?:-<?>?))/';

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
        $lines = $this->splitLineBreaks($cypherPattern);

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

    public function splitLineBreaks($cypherPattern)
    {
        $lines = explode("\n", $cypherPattern);
        $parsedLines = [];
        foreach ($lines as $line) {
            if (false === strpos($line, '//')) {
                $parsedLines[] = htmlspecialchars_decode($line);
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
        if (!$nodeInfo['label'] && !$nodeInfo['properties']) {
            if (!array_key_exists($nodeInfo['identifier'], $this->identifiers)){
                throw new SchemaException(sprintf('The identifer "%s" has not been declared', $nodeInfo['identifier']));
            }
            return;
        }

        $label = $nodeInfo['label'];
        if (in_array($label, $this->labels)) {
            return;
        }
        $node = [
            'label' => $label,
            'count' => $nodeInfo['count'],
            'properties' => $nodeInfo['properties']
        ];
        if (null !== $nodeInfo['identifier']) {
            $this->identifiers[$nodeInfo['identifier']] = $label;
            if (null !== $label) {
                $virtualPart = '('.$nodeInfo['identifier'].')';
                $this->nodeInfoMap[$virtualPart] = $nodeInfo;
            }

            if (null !== $part && null !== $nodeInfo['label']) {
                $this->nodeInfoMap[trim($part)] = $nodeInfo;
            }
        }

        if ($nodeInfo['properties']){

            try {
                $props = Yaml::parse($node['properties']);
                $node['properties'] = $props;
            } catch (ParseException $e){
                throw new CypherPatternException(sprintf('Malformed inline properties near "%s"', $node['properties']));
            }
        }

        $this->nodes[] = $node;
        $this->labels[] = $label;
    }

    public function processEdge(array $edgeInfo, $key, array $parts)
    {
        $prev = trim($parts[$key-1]);
        $previous = $this->nodeInfoMap[trim($prev)];
        $prevNode = !empty($previous['label']) ? $previous['label'] : $this->identifiers[$previous['identifier']];

        $next = trim($parts[$key+1]);

        preg_match(self::NODE_PATTERN, $next, $output);
        $info = $this->getNodePatternInfo($output, $next);
        $this->processNode($info);

        $nextious = $this->nodeInfoMap[trim($next)];
        $nextNode = !empty($nextious['label']) ? $nextious['label'] : $this->identifiers[$nextious['identifier']];

        $start = 'OUT' === $edgeInfo['direction'] ? $prevNode : $nextNode;
        $end = 'OUT' === $edgeInfo['direction'] ? $nextNode : $prevNode;

        $edge = [
            'start' => $start,
            'end' => $end,
            'type' => $edgeInfo['type'],
            'mode' => $edgeInfo['cardinality'],
        ];

        try {
            $edge['properties'] = Yaml::parse($edgeInfo['properties']);
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

        $defaultInfo = [
            'identifier' => $this->nullString($nodePattern[3]),
            'label' => $this->nullString($nodePattern[5]),
            'properties' => $this->nullString($nodePattern[6]),
            'count' => $this->nullString($nodePattern[7])
        ];

        if (!$defaultInfo['identifier'] && !$defaultInfo['label']) {
            throw new SchemaException(sprintf('You must use or a label or an identifier near "%s"', $nodePattern));
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
            throw new SchemaException(sprintf('The direction of the relationship must be defined, near "%s".', $edgePattern));
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
        $cardinality = trim($pattern[4]);
        if (!in_array($cardinality, $allowedCardinalities)) {
            throw new SchemaException(sprintf('The cardinality "%s" is not allowed', $cardinality));
        }

        return $cardinality;
    }

    public function getSchema()
    {
        $schema = [
            'nodes' => $this->nodes,
            'relationships' => $this->edges
        ];

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

        return trim($string);
    }
}